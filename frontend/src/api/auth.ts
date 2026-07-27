import { csrf, http } from './http'
import type { AuthUser } from '@/types/user'

export async function login(email: string, password: string): Promise<AuthUser> {
    await csrf()
    const { data } = await http.post<{ user: AuthUser }>('/login', { email, password })
    return data.user
}

export async function logout(): Promise<void> {
    await http.post('/logout')
}
