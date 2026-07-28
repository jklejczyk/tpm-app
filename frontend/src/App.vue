<script setup lang="ts">
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { ROLE_LABEL } from '@/constants/user'

const auth = useAuthStore()
const router = useRouter()

async function logout() {
    await auth.logout()
    router.push({ name: 'login' })
}
</script>

<template>
    <header v-if="auth.isAuthenticated" class="topbar">
        <strong>TPM</strong>
        <nav class="nav">
            <RouterLink :to="{ name: 'work-orders' }">Work Orders</RouterLink>
            <RouterLink :to="{ name: 'production' }">Production</RouterLink>
            <RouterLink :to="{ name: 'oee' }">OEE</RouterLink>
        </nav>
        <span class="who"
            >{{ auth.user?.name }} - {{ auth.user ? ROLE_LABEL[auth.user.role] : '' }}</span
        >
        <button class="ghost" @click="logout">Sign out</button>
    </header>

    <main>
        <RouterView />
    </main>
</template>
