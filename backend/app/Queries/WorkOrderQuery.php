<?php

namespace App\Queries;

use App\Models\WorkOrderModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;


final class WorkOrderQuery
{
    /**
     * @var list<string>
     */
    public const SORTABLE = ['reported_at', 'status', 'machine_id', 'reason', 'assigned_to'];

    /**
     * @return LengthAwarePaginator<WorkOrderModel>
     */
    public function paginate(int $perPage, string $sort, string $direction): LengthAwarePaginator
    {
        $query = WorkOrderModel::query();

        if ($sort === 'assigned_to') {
            $query
                ->leftJoin('users', function (JoinClause $join) {
                    $join->on(DB::raw('cast(users.id as text)'), '=', 'work_orders.assigned_to');
                })
                ->orderBy('users.name', $direction)
                ->select('work_orders.*');
        } else {
            $query->orderBy('work_orders.'.$sort, $direction);
        }

        return $query
            ->orderBy('work_orders.id') // deterministic tiebreaker so pages never overlap
            ->paginate($perPage);
    }
}
