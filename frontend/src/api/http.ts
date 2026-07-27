import axios, { AxiosError } from 'axios'

const BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8082/api/v1'
const TOKEN_KEY = 'tpm.token'

let token: string | null = localStorage.getItem(TOKEN_KEY)
let onUnauthorized: (() => void) | null = null

export function setToken(value: string | null): void {
    token = value
    if (value) localStorage.setItem(TOKEN_KEY, value)
    else localStorage.removeItem(TOKEN_KEY)
}

export function getToken(): string | null {
    return token
}

export function setUnauthorizedHandler(handler: () => void): void {
    onUnauthorized = handler
}

export class ApiError extends Error {
    constructor(
        public readonly status: number,
        message: string,
    ) {
        super(message)
    }
}

export function friendlyMessage(error: unknown): string {
    if (!(error instanceof ApiError)) return 'Something went wrong. Please try again.'
    if (error.status === 0) return 'Network error. Please check your connection.'
    if (error.status >= 500) return 'Something went wrong. Please try again.'
    return error.message
}

export const http = axios.create({
    baseURL: BASE,
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
})

http.interceptors.request.use((config) => {
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
})

http.interceptors.response.use(
    (response) => response,
    (error: AxiosError<{ message?: string }>) => {
        const status = error.response?.status ?? 0
        // 401 only reaches here for a rejected token — bad logins return 422.
        if (status === 401 && token !== null) onUnauthorized?.()
        const message = error.response?.data?.message ?? error.message
        return Promise.reject(new ApiError(status, message))
    },
)
