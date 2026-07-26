<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useWorkOrdersStore } from '@/stores/workOrders'
import { REASON_LABEL, STATUS_LABEL } from '@/constants/workOrder'
import type { WorkOrderReason, WorkOrderSortField } from '@/types/workOrder'

const store = useWorkOrdersStore()
const router = useRouter()

const machineId = ref('')
const reason = ref<WorkOrderReason>('breakdown')
const reportError = ref<string | null>(null)

const SKELETON_ROWS = 10
const COLUMNS = 6

onMounted(() => store.fetchList())

async function report() {
    reportError.value = null
    try {
        await store.report(machineId.value, reason.value)
        machineId.value = ''
    } catch (e) {
        reportError.value = (e as Error).message
    }
}

function open(id: string) {
    router.push({ name: 'work-order', params: { id } })
}

function sortIndicator(field: WorkOrderSortField): string {
    if (store.sort !== field) return ''
    return store.direction === 'asc' ? ' ▲' : ' ▼'
}

function formatDate(iso: string | null): string {
    if (!iso) return '—'
    const d = new Date(iso)
    const pad = (n: number) => String(n).padStart(2, '0')
    return `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.${d.getFullYear()}, ${pad(d.getHours())}:${pad(d.getMinutes())}`
}
</script>

<template>
    <h2>Work orders</h2>

    <form class="toolbar" @submit.prevent="report">
        <input v-model="machineId" placeholder="machine id (e.g. m-1)" />
        <select v-model="reason">
            <option value="breakdown">Breakdown</option>
            <option value="inspection">Inspection</option>
            <option value="operator_report">Operator report</option>
        </select>
        <button>Report fault</button>
    </form>
    <p v-if="reportError" class="error">{{ reportError }}</p>

    <p v-if="store.error" class="error">{{ store.error }}</p>
    <template v-else>
        <div class="wide">
            <table>
                <thead>
                    <tr>
                        <th class="sortable" @click="store.setSort('status')">
                            Status{{ sortIndicator('status') }}
                        </th>
                        <th>ID</th>
                        <th class="sortable" @click="store.setSort('machine_id')">
                            Machine{{ sortIndicator('machine_id') }}
                        </th>
                        <th class="sortable" @click="store.setSort('reason')">
                            Reason{{ sortIndicator('reason') }}
                        </th>
                        <th class="sortable" @click="store.setSort('assigned_to')">
                            Assigned to{{ sortIndicator('assigned_to') }}
                        </th>
                        <th class="sortable" @click="store.setSort('reported_at')">
                            Reported{{ sortIndicator('reported_at') }}
                        </th>
                    </tr>
                </thead>

                <tbody v-if="store.loading" aria-hidden="true">
                    <tr v-for="n in SKELETON_ROWS" :key="`skeleton-${n}`" class="skeleton-row">
                        <td v-for="c in COLUMNS" :key="c"><span class="skeleton"></span></td>
                    </tr>
                </tbody>

                <tbody v-else-if="store.total === 0">
                    <tr>
                        <td :colspan="COLUMNS" class="empty">
                            No work orders. Report the first fault above.
                        </td>
                    </tr>
                </tbody>

                <tbody v-else>
                    <tr v-for="wo in store.list" :key="wo.id" @click="open(wo.id)">
                        <td>
                            <span class="pill" :data-status="wo.status">{{
                                STATUS_LABEL[wo.status]
                            }}</span>
                        </td>
                        <td class="mono">{{ wo.id }}</td>
                        <td class="mono">{{ wo.machineId }}</td>
                        <td>{{ REASON_LABEL[wo.reason] }}</td>
                        <td class="mono">{{ wo.assignedToName ?? wo.assignedTo ?? '—' }}</td>
                        <td>{{ formatDate(wo.reportedAt) }}</td>
                    </tr>
                </tbody>
            </table>

            <nav v-if="!store.loading && store.total > 0" class="pager">
                <button :disabled="store.page <= 1" @click="store.goToPage(store.page - 1)">
                    ‹ Prev
                </button>
                <span>Page {{ store.page }} of {{ store.lastPage }} · {{ store.total }} total</span>
                <button
                    :disabled="store.page >= store.lastPage"
                    @click="store.goToPage(store.page + 1)"
                >
                    Next ›
                </button>
            </nav>
        </div>
    </template>
</template>

<style scoped>
.sortable {
    cursor: pointer;
    user-select: none;
}

.wide {
    margin-inline: calc(50% - 50vw);
    padding-inline: 1.25rem;
    overflow-x: auto;
}

.pager {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-top: 1rem;
}

.pager button:disabled {
    opacity: 0.5;
    cursor: default;
}

.empty {
    text-align: center;
    padding: 1.5rem;
    opacity: 0.7;
}

.skeleton-row {
    cursor: default;
}

.skeleton {
    display: inline-block;
    width: 70%;
    height: 0.85em;
    border-radius: 4px;
    background: linear-gradient(90deg, #e6e6e6 25%, #f0f0f0 50%, #e6e6e6 75%);
    background-size: 200% 100%;
    animation: skeleton-shimmer 1.2s ease-in-out infinite;
}

.skeleton-row td:first-child .skeleton {
    width: 4rem;
    height: 1.1em;
    border-radius: 999px;
}

@keyframes skeleton-shimmer {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .skeleton {
        animation: none;
    }
}
</style>
