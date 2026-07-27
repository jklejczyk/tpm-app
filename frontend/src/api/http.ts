import axios, { AxiosError } from 'axios'

// Derive the API host from the page host so cookies match whether the SPA is
// opened on localhost or 127.0.0.1 (the browser treats them as distinct hosts).
const API_PORT = 8082
const BASE =
    import.meta.env.VITE_API_URL ??
    `${window.location.protocol}//${window.location.hostname}:${API_PORT}/api/v1`
const ORIGIN = new URL(BASE).origin

let onUnauthorized: (() => void) | null = null

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
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
})

export function csrf(): Promise<void> {
    return http.get('/sanctum/csrf-cookie', { baseURL: ORIGIN }).then(() => undefined)
}

http.interceptors.response.use(
    (response) => response,
    (error: AxiosError<{ message?: string }>) => {
        const status = error.response?.status ?? 0
        if (status === 401) onUnauthorized?.()
        const message = error.response?.data?.message ?? error.message
        return Promise.reject(new ApiError(status, message))
    },
)
