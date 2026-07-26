import { http } from './http'
import type { Wrapped } from '@/types/api'
import type { Role, UserSummary } from '@/types/user'

export async function listUsers(role: Role): Promise<UserSummary[]> {
    const { data } = await http.get<Wrapped<UserSummary[]>>(`/users/role/${role}`)
    return data.data
}
