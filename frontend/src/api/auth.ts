import { http } from './http'
import type { AuthUser } from '@/types/workOrder'

export interface LoginResponse {
    token: string
    user: AuthUser
}

export async function login(email: string, password: string): Promise<LoginResponse> {
    const { data } = await http.post<LoginResponse>('/login', { email, password })
    return data
}

export async function logout(): Promise<void> {
    await http.post('/logout')
}
