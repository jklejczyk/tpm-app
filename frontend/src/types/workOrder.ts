export type WorkOrderStatus =
    'reported' | 'assigned' | 'in_progress' | 'on_hold' | 'resolved' | 'closed'

export type WorkOrderReason = 'breakdown' | 'inspection' | 'operator_report'

export type TransitionName = 'assign' | 'start' | 'hold' | 'resume' | 'resolve' | 'close'

export interface WorkOrder {
    id: string
    machineId: string
    status: WorkOrderStatus
    reason: WorkOrderReason
    reportedBy: string
    reportedByName: string | null
    assignedTo: string | null
    assignedToName: string | null
    resolution: string | null
    holdReason: string | null
    reportedAt: string | null
}

export type SortDirection = 'asc' | 'desc'

export type WorkOrderSortField = 'reported_at' | 'status' | 'machine_id' | 'reason' | 'assigned_to'

export interface PageMeta {
    currentPage: number
    lastPage: number
    perPage: number
    total: number
}

export interface Paginated<T> {
    data: T[]
    meta: PageMeta
}
