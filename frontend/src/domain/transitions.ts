import type { Role, TransitionName, WorkOrder } from '@/types/workOrder'

export interface AllowedTransition {
    name: TransitionName
    label: string
    needs?: 'technician_id' | 'reason' | 'resolution'
}

export function allowedTransitions(wo: WorkOrder, role: Role, userId: string): AllowedTransition[] {
    const isAssignee = wo.assignedTo === userId

    switch (wo.status) {
        case 'reported':
            return role === 'manager' || role === 'technician'
                ? [{ name: 'assign', label: 'Assign', needs: 'technician_id' }]
                : []
        case 'assigned':
            return role === 'technician' && isAssignee ? [{ name: 'start', label: 'Start' }] : []
        case 'in_progress':
            return role === 'technician'
                ? [
                      { name: 'hold', label: 'Hold', needs: 'reason' },
                      { name: 'resolve', label: 'Resolve', needs: 'resolution' },
                  ]
                : []
        case 'on_hold':
            return role === 'technician' ? [{ name: 'resume', label: 'Resume' }] : []
        case 'resolved':
            return role === 'manager' ? [{ name: 'close', label: 'Close' }] : []
        default:
            return []
    }
}
