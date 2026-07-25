import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: () => import('@/views/LoginView.vue'),
        },
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
    ],
})

router.beforeEach((to) => {
    const auth = useAuthStore()
    if (to.name !== 'login' && !auth.isAuthenticated) return { name: 'login' }
    if (to.name === 'login' && auth.isAuthenticated) return { name: 'work-orders' }
})

export default router
