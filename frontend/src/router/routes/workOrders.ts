import type { RouteRecordRaw } from 'vue-router'

export const workOrderRoutes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'work-orders',
        component: () => import('@/views/WorkOrdersView.vue'),
    },
    {
        path: '/work-orders/:id',
        name: 'work-order',
        component: () => import('@/views/WorkOrderDetailView.vue'),
        props: true,
    },
]
