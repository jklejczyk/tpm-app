import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { workOrderRoutes } from '@/router/routes/workOrders'

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: () => import('@/views/LoginView.vue'),
        },
        ...workOrderRoutes,
    ],
})

router.beforeEach((to) => {
    const auth = useAuthStore()
    if (to.name !== 'login' && !auth.isAuthenticated) return { name: 'login' }
    if (to.name === 'login' && auth.isAuthenticated) return { name: 'work-orders' }
})

export default router
