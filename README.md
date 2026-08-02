# TPM — Total Productive Maintenance

![Tests](https://github.com/jklejczyk/tpm-app/actions/workflows/backend-tests.yaml/badge.svg)
![Pint](https://github.com/jklejczyk/tpm-app/actions/workflows/backend-pint.yaml/badge.svg)
![Security](https://github.com/jklejczyk/tpm-app/actions/workflows/backend-security.yaml/badge.svg)
[![codecov](https://codecov.io/gh/jklejczyk/tpm-app/graph/badge.svg)](https://codecov.io/gh/jklejczyk/tpm-app)
![PHPStan](https://img.shields.io/badge/PHPStan-level%209-brightgreen)

A full-stack application for managing machine breakdowns and production on a shop floor. Operators report faults as **Work Orders**, which move through a status state machine (`reported → assigned → in_progress → on_hold/resolved → closed`) under role-based permissions (operator / technician / manager). Production data is recorded per machine and used to derive **OEE** (Availability × Performance × Quality) on demand, correlated against Work Order downtime. A breakdown report also triggers an asynchronous email notification to managers.

The system is built as a **Laravel REST API** + **Vue 3 SPA**, both sitting on top of a separate, framework-agnostic domain library — [`jklejczyk/tpm-core`](https://github.com/jklejczyk/tpm-core) — pulled in via Composer. This repository is the application layer (the "adapter"); the domain rules live entirely outside of Laravel.

## Tech stack

**Backend**
- PHP 8.3, Laravel 13
- PostgreSQL (primary datastore)
- Redis (queue + cache backend)
- Laravel Sanctum (SPA cookie session auth + CSRF)
- Pest 4 (testing), Larastan 3 / PHPStan level 9, Laravel Pint (style)
- Served via nginx + php-fpm

**Frontend**
- Vue 3 + TypeScript, Vite
- Pinia (state), Vue Router, Axios
- Chart.js / vue-chartjs (OEE visualizations)
- ESLint + oxlint + Prettier, vue-tsc (type-check)

**Domain**
- [`jklejczyk/tpm-core`](https://github.com/jklejczyk/tpm-core) — a plain-PHP domain library (`Tpm\` namespace), no Laravel dependency

**Infrastructure**
- Docker Compose: `nginx`, `backend` (php-fpm), `queue` (worker), `frontend` (Vite dev server), `postgres`, `redis`

## Architecture

The application follows a **ports-and-adapters (hexagonal) architecture**: dependencies point inward, from Laravel toward the domain. The domain (`tpm-core`) never imports Illuminate/Symfony classes — this boundary is enforced with `deptrac` in the domain repository.

- **Entity ≠ Eloquent model.** Domain aggregates such as `WorkOrder` are plain PHP objects that encapsulate the state machine and invariants. Persistence is handled by separate, intentionally anemic Eloquent models (`WorkOrderModel`, `ProductionRecordModel`, `MachineModel`) plus a `Mapper` collaborator (`App\Mappers`) that translates row ↔ entity on the write path.
- **CQRS-style read/write split.** Commands (state transitions) load the aggregate through a repository port, mutate it via a named method (`$workOrder->assign(...)`, `->resolve()`, ...), and persist it via `save()`. Reads never touch the aggregate — they go through `App\Queries\*` (Eloquent projections with eager-loaded relations) and are serialized directly by `App\Http\Resources`. Command responses re-read the projection after the write (read-after-write), so list, `show`, and command endpoints share one serialization path.
- **Authorization is a domain rule.** Permissions (who can assign, start, hold, resolve, close a Work Order) are enforced inside the entity via an `Actor` (id + role) passed into its methods — not via Laravel Gates or middleware. `ActorFactory` builds the `Actor` from the authenticated user at the framework boundary.
- **Repositories are ports.** Interfaces (`WorkOrderRepository`, `ProductionRecordRepository`, `MachineRepository`) live in the domain; Eloquent adapters implement them and are bound in `App\Providers\TpmServiceProvider`.
- **Domain exceptions are mapped to HTTP centrally**, in `bootstrap/app.php` (`withExceptions`) — e.g. `UnauthorizedTransition` → 403, `IllegalStateTransition` / `AssigneeMustBeTechnician` / `MissingHoldReason` / `MissingResolution` / `ResolvedBeforeReported` → 422, `WorkOrderNotFound` / `ProductionRecordNotFound` → 404. Controllers stay thin, with no `try/catch`.
- **OEE is computed on read**, from production records and Work Order downtime for the requested machine/window — no separate projection or job needed for it.
- **Asynchronous showcase:** reporting a breakdown makes the `WorkOrder` aggregate record a `WorkOrderReported` domain event (plain PHP). The repository dispatches it as a Laravel event after persisting (`EloquentWorkOrderRepository::save()`), a sync listener (`SendBreakdownAlert`) sends a queued (`ShouldQueue`) email notification to all managers, delivered through the Redis-backed queue worker.
- **Authentication** uses Sanctum's SPA cookie-session flow (not bearer tokens): CSRF cookie + session cookie, `SameSite` + CORS with credentials. The login endpoint is rate-limited (5 attempts/minute per email+IP).

## Getting started

### Prerequisites
- Docker and Docker Compose

### Run it

```bash
# 1. Create the environment file — it must exist BEFORE the stack starts,
#    because docker-compose bind-mounts backend/.env into the containers.
cp backend/.env.example backend/.env

# 2. Build and start all services
docker compose up -d --build

# 3. Install PHP dependencies.
docker compose exec backend composer install
docker compose restart backend queue

# 4. Generate the application key
docker compose exec backend php artisan key:generate

# 5. Run database migrations and seed demo data
docker compose exec backend php artisan migrate:fresh --seed
```

- Frontend (SPA): **http://localhost:5173**
- API (via nginx): **http://localhost:8082/api/v1**
- Postgres: exposed on host port **5433**
- Redis: exposed on host port **6380**

### Seeded accounts

All seeded users share the password `password`:

| Email                       | Role       |
|------------------------------|------------|
| `operator@example.com`       | Operator   |
| `technician@example.com`     | Technician |
| `manager@example.com`        | Manager    |

### Trying the API by hand

Authentication is cookie-based (Sanctum SPA flow), not bearer tokens. Every call needs the
session cookie, the CSRF header on writes, and an `Origin` header from the configured frontend
domain — without it Sanctum ignores the session and answers `401`.

Log in. Cookies are kept in `cookies.txt`, and the CSRF token is read back from that file:

```bash
curl -s -c cookies.txt -o /dev/null http://localhost:8082/sanctum/csrf-cookie

curl -s -b cookies.txt -c cookies.txt -X POST http://localhost:8082/api/v1/login \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -H 'Origin: http://localhost:5173' \
  -H "X-XSRF-TOKEN: $(grep XSRF-TOKEN cookies.txt | awk '{print $7}' | sed 's/%3D/=/g')" \
  -d '{"email":"operator@example.com","password":"password"}'
```

Reading data:

```bash
curl -s -b cookies.txt -H 'Accept: application/json' -H 'Origin: http://localhost:5173' \
  http://localhost:8082/api/v1/machines

curl -s -b cookies.txt -H 'Accept: application/json' -H 'Origin: http://localhost:5173' \
  http://localhost:8082/api/v1/work-orders

curl -s -b cookies.txt -H 'Accept: application/json' -H 'Origin: http://localhost:5173' \
  http://localhost:8082/api/v1/production-records

curl -s -b cookies.txt -H 'Accept: application/json' -H 'Origin: http://localhost:5173' \
  http://localhost:8082/api/v1/users/role/technician
```

Report a breakdown as the operator. The request field is `machine_id` in snake case, while
responses use camel case. Take a machine id from `/machines` above:

```bash
curl -s -b cookies.txt -c cookies.txt -X POST http://localhost:8082/api/v1/work-orders \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -H 'Origin: http://localhost:5173' \
  -H "X-XSRF-TOKEN: $(grep XSRF-TOKEN cookies.txt | awk '{print $7}' | sed 's/%3D/=/g')" \
  -d '{"machine_id":"PASTE_MACHINE_ID","reason":"breakdown"}'
```

The rest of the lifecycle. Log in again as the manager, then as the technician, using the login
command above with the matching e-mail; every user shares the password `password`. Replace
`PASTE_WORK_ORDER_ID` with the id returned above:

```bash
# manager assigns the technician
curl -s -b cookies.txt -c cookies.txt -X POST \
  http://localhost:8082/api/v1/work-orders/PASTE_WORK_ORDER_ID/assign \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -H 'Origin: http://localhost:5173' \
  -H "X-XSRF-TOKEN: $(grep XSRF-TOKEN cookies.txt | awk '{print $7}' | sed 's/%3D/=/g')" \
  -d '{"technician_id":"2"}'

# technician starts, holds, resumes and resolves
curl -s -b cookies.txt -c cookies.txt -X POST \
  http://localhost:8082/api/v1/work-orders/PASTE_WORK_ORDER_ID/start \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -H 'Origin: http://localhost:5173' \
  -H "X-XSRF-TOKEN: $(grep XSRF-TOKEN cookies.txt | awk '{print $7}' | sed 's/%3D/=/g')" \
  -d '{}'

curl -s -b cookies.txt -c cookies.txt -X POST \
  http://localhost:8082/api/v1/work-orders/PASTE_WORK_ORDER_ID/hold \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -H 'Origin: http://localhost:5173' \
  -H "X-XSRF-TOKEN: $(grep XSRF-TOKEN cookies.txt | awk '{print $7}' | sed 's/%3D/=/g')" \
  -d '{"reason":"Spare part unavailable"}'

curl -s -b cookies.txt -c cookies.txt -X POST \
  http://localhost:8082/api/v1/work-orders/PASTE_WORK_ORDER_ID/resume \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -H 'Origin: http://localhost:5173' \
  -H "X-XSRF-TOKEN: $(grep XSRF-TOKEN cookies.txt | awk '{print $7}' | sed 's/%3D/=/g')" \
  -d '{}'

curl -s -b cookies.txt -c cookies.txt -X POST \
  http://localhost:8082/api/v1/work-orders/PASTE_WORK_ORDER_ID/resolve \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -H 'Origin: http://localhost:5173' \
  -H "X-XSRF-TOKEN: $(grep XSRF-TOKEN cookies.txt | awk '{print $7}' | sed 's/%3D/=/g')" \
  -d '{"resolution":"Bearing replaced"}'

# manager closes it
curl -s -b cookies.txt -c cookies.txt -X POST \
  http://localhost:8082/api/v1/work-orders/PASTE_WORK_ORDER_ID/close \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -H 'Origin: http://localhost:5173' \
  -H "X-XSRF-TOKEN: $(grep XSRF-TOKEN cookies.txt | awk '{print $7}' | sed 's/%3D/=/g')" \
  -d '{}'
```

The refusals are the point of the state machine. Each returns a different code for a different
reason:

- no session at all — `401`
- an operator trying to assign a technician, or a technician trying to close — `403`, refused by
  the aggregate rather than by middleware
- a transition that is illegal from the current state, such as `resume` on a work order that is
  not on hold — `422`

Try any of them by logging in as a different user and repeating the matching command above.

**OEE takes the exact boundaries of a production record**, not an arbitrary date range: `from`
and `to` must equal that record's `period_start` and `period_end`, otherwise the endpoint answers
`404`. Read them from `/production-records` first. The `+` of a timezone offset has to be
percent-encoded as `%2B` in a query string:

```bash
curl -s -b cookies.txt -H 'Accept: application/json' -H 'Origin: http://localhost:5173' \
  'http://localhost:8082/api/v1/machines/PASTE_MACHINE_ID/oee?from=2026-07-15T08:00:00%2B00:00&to=2026-07-15T16:00:00%2B00:00'
```

The seeder generates machine ids and periods randomly, so paste the values printed by
`/production-records`.

### Queue worker

Breakdown notifications are delivered asynchronously. The `queue` container runs `php artisan queue:listen` against Redis. With the default `MAIL_MAILER=log`, reporting a breakdown writes the manager email to `backend/storage/logs/laravel.log` instead of sending it over SMTP.

## Testing & quality

**Backend** (run inside the `backend` container; tests run against Postgres, not SQLite):

```bash
docker compose exec backend php artisan test        # Pest
docker compose exec backend composer run phpstan     # Larastan, level 9
docker compose exec backend vendor/bin/pint          # code style
```

**Frontend**:

```bash
docker compose exec frontend npm run type-check
docker compose exec frontend npm run lint
docker compose exec frontend npm run format
```

## Domain library

The business rules — Work Order state machine, value objects (`WorkOrderId`, `MachineId`, `UserId`), roles, domain exceptions, and repository ports — live in a separate repository, [`tpm-core`](https://github.com/jklejczyk/tpm-core) (`Tpm\` namespace), consumed here as the `jklejczyk/tpm-core` Composer package. It has no dependency on Laravel and is tested, statically analyzed, and architecture-checked in isolation. This application supplies the "adapters": Eloquent-backed repository implementations, HTTP controllers/requests/resources, and the exception-to-HTTP mapping.

## Known gaps

**The OEE endpoint matches a production period exactly instead of querying a range.**
`ProductionRecordQuery::forWindow()` compares `period_start` and `period_end` for equality, so
`from` and `to` have to line up with an existing record to the second. A natural question such
as "what was this machine's OEE in July" cannot be asked: it answers `404`, which reads like
missing data rather than an unsupported query. The method name and the `from`/`to` parameters
both promise a range, so the naming is misleading as well. This should be reworked into a real
window query that aggregates the records inside the range — planned time, downtime and unit
counts summed across them — which is a change to how OEE is computed, not just to the query.

## A note on scope

I did not focus on the appearance of the application, because this is a test application and the
goal here was to present the architecture.