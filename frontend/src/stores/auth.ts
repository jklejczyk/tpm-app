import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import * as authApi from '@/api/auth'
import { getToken, setToken } from '@/api/http'
import type { AuthUser } from '@/types/workOrder'

const USER_KEY = 'tpm.user'

export const useAuthStore = defineStore('auth', () => {
    const saved = localStorage.getItem(USER_KEY)
    const user = ref<AuthUser | null>(saved ? (JSON.parse(saved) as AuthUser) : null)

    const isAuthenticated = computed(() => user.value !== null && getToken() !== null)

    async function login(email: string, password: string): Promise<void> {
        const res = await authApi.login(email, password)
        setToken(res.token)
        user.value = res.user
        localStorage.setItem(USER_KEY, JSON.stringify(res.user))
    }

    async function logout(): Promise<void> {
        try {
            await authApi.logout()
        } catch {
            // silent
        }
        setToken(null)
        user.value = null
        localStorage.removeItem(USER_KEY)
    }

    return { user, isAuthenticated, login, logout }
})
