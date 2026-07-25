<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const email = ref('')
const password = ref('')
const error = ref<string | null>(null)
const loading = ref(false)

const auth = useAuthStore()
const router = useRouter()

async function submit() {
    loading.value = true
    error.value = null
    try {
        await auth.login(email.value, password.value)
        router.push({ name: 'work-orders' })
    } catch {
        error.value = 'Invalid email or password.'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="login">
        <h1>TPM</h1>
        <form @submit.prevent="submit">
            <input v-model="email" type="email" placeholder="email" autofocus />
            <input v-model="password" type="password" placeholder="password" />
            <button :disabled="loading">{{ loading ? 'Signing in…' : 'Sign in' }}</button>
            <p v-if="error" class="error">{{ error }}</p>
        </form>
    </div>
</template>

<style scoped>
.login {
    max-width: 320px;
    margin: 4rem auto;
}
.login h1 {
    margin-bottom: 0.25rem;
    letter-spacing: 0.04em;
}
.muted {
    color: var(--muted);
    margin-top: 0;
}
form {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin-top: 1.5rem;
}
</style>
