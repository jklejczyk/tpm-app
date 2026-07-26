export type Role = 'operator' | 'technician' | 'manager'

export interface AuthUser {
    id: string
    name: string
    role: Role
}

export interface UserSummary {
    id: string
    name: string
}
