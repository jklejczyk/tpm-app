<?php

namespace App\Queries;

use App\Models\WorkOrderModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class WorkOrderQuery
{
    /**
     * @var list<string>
     */
    public const SORTABLE = ['reported_at', 'status', 'machine_id', 'reason', 'assigned_to'];

    /**
     * @var list<string>
     */
    private const RELATIONS = ['reporter', 'assignee'];

    public function find(string $id): WorkOrderModel
    {
        return WorkOrderModel::query()->with(self::RELATIONS)->findOrFail($id);
    }

    /**
     * @return LengthAwarePaginator<WorkOrderModel>
     */
    public function paginate(int $perPage, string $sort, string $direction): LengthAwarePaginator
    {
        $query = WorkOrderModel::query()->with(self::RELATIONS);

        if ($sort === 'assigned_to') {
            $query
                ->leftJoin('users', 'users.id', '=', 'work_orders.assigned_to')
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
