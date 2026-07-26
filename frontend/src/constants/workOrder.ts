import type { WorkOrderReason, WorkOrderStatus } from '@/types/workOrder'

export const STATUS_LABEL: Record<WorkOrderStatus, string> = {
    reported: 'Reported',
    assigned: 'Assigned',
    in_progress: 'In progress',
    on_hold: 'On hold',
    resolved: 'Resolved',
    closed: 'Closed',
}

export const REASON_LABEL: Record<WorkOrderReason, string> = {
    breakdown: 'Breakdown',
    inspection: 'Inspection',
    operator_report: 'Operator report',
}

export const LIFECYCLE: WorkOrderStatus[] = [
    'reported',
    'assigned',
    'in_progress',
    'on_hold',
    'resolved',
    'closed',
]
