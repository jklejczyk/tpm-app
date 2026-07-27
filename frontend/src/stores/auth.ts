import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import * as authApi from '@/api/auth'
import type { AuthUser } from '@/types/user'

const USER_KEY = 'tpm.user'

function isAuthUser(value: unknown): value is AuthUser {
    if (typeof value !== 'object' || value === null) return false
    const u = value as Record<string, unknown>
    return typeof u.id === 'string' && typeof u.name === 'string' && typeof u.role === 'string'
}

function readStoredUser(): AuthUser | null {
    const raw = localStorage.getItem(USER_KEY)
    if (raw === null) return null
    try {
        const parsed: unknown = JSON.parse(raw)
        if (isAuthUser(parsed)) return parsed
    } catch {
        // corrupted JSON
    }
    localStorage.removeItem(USER_KEY)
    return null
}

export const useAuthStore = defineStore('auth', () => {
    const user = ref<AuthUser | null>(readStoredUser())

    const isAuthenticated = computed(() => user.value !== null)

    async function login(email: string, password: string): Promise<void> {
        const authUser = await authApi.login(email, password)
        user.value = authUser
        localStorage.setItem(USER_KEY, JSON.stringify(authUser))
    }

    function clearSession(): void {
        user.value = null
        localStorage.removeItem(USER_KEY)
    }

    async function logout(): Promise<void> {
        try {
            await authApi.logout()
        } catch {
            // silent
        }
        clearSession()
    }

    return { user, isAuthenticated, login, logout, clearSession }
})
