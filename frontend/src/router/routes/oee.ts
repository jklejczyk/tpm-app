import type { RouteRecordRaw } from 'vue-router'

export const oeeRoutes: RouteRecordRaw[] = [
    {
        path: '/oee',
        name: 'oee',
        component: () => import('@/views/OeeDashboardView.vue'),
    },
    {
        path: '/production',
        name: 'production',
        component: () => import('@/views/ProductionView.vue'),
    },
]
