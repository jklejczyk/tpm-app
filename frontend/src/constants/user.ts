import type { Role } from '@/types/user'

export const ROLE_LABEL: Record<Role, string> = {
    operator: 'Operator',
    technician: 'Technician',
    manager: 'Manager',
}
