import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { setUnauthorizedHandler } from '@/api/http'
import { useAuthStore } from '@/stores/auth'

const app = createApp(App)

app.use(createPinia())
app.use(router)

setUnauthorizedHandler(() => {
    useAuthStore().clearSession()
    if (router.currentRoute.value.name !== 'login') {
        void router.push({ name: 'login' })
    }
})

app.mount('#app')
