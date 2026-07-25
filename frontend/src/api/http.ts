import axios, { AxiosError } from 'axios'

const BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8082/api/v1'
const TOKEN_KEY = 'tpm.token'

let token: string | null = localStorage.getItem(TOKEN_KEY)

export function setToken(value: string | null): void {
    token = value
    if (value) localStorage.setItem(TOKEN_KEY, value)
    else localStorage.removeItem(TOKEN_KEY)
}

export function getToken(): string | null {
    return token
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
    if (error instanceof ApiError) {
        return error.status >= 500 ? 'Something went wrong. Please try again.' : error.message
    }
    return 'Network error. Please check your connection.'
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
        const message = error.response?.data?.message ?? error.message
        return Promise.reject(new ApiError(status, message))
    },
)
