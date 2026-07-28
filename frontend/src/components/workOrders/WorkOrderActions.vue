<script setup lang="ts">
import { ref, watch } from 'vue'
import { useWorkOrdersStore } from '@/stores/workOrders'
import { useUsersStore } from '@/stores/users'
import type { AllowedTransition } from '@/domain/transitions'
import { friendlyMessage } from '@/api/http'

const props = defineProps<{ id: string; actions: AllowedTransition[] }>()

const store = useWorkOrdersStore()
const users = useUsersStore()

const error = ref<string | null>(null)
const inputs = ref<Record<string, string>>({})
const submitting = ref<string | null>(null)

const PLACEHOLDER: Partial<Record<NonNullable<AllowedTransition['needs']>, string>> = {
    reason: 'hold reason',
    resolution: 'resolution',
}

function placeholderFor(action: AllowedTransition): string {
    return action.needs ? (PLACEHOLDER[action.needs] ?? '') : ''
}

watch(
    () => props.actions,
    (actions) => {
        actions.forEach((a) => {
            if (a.needs && inputs.value[a.name] === undefined) {
                inputs.value[a.name] = ''
            }
        })

        if (actions.some((a) => a.needs === 'technician_id')) {
            void users.fetch('technician')
        }
    },
    { immediate: true },
)

async function run(action: AllowedTransition) {
    error.value = null
    const payload: Record<string, string> = {}

    if (action.needs) {
        const value = (inputs.value[action.name] ?? '').trim()
        if (!value) {
            error.value = `Enter a value before you ${action.label.toLowerCase()}.`
            return
        }
        payload[action.needs] = value
    }

    submitting.value = action.name
    try {
        await store.applyTransition(props.id, action.name, payload)
        inputs.value[action.name] = ''
    } catch (e) {
        error.value = friendlyMessage(e)
    } finally {
        submitting.value = null
    }
}
</script>

<template>
    <h3>Actions</h3>
    <div v-if="actions.length" class="actions">
        <form v-for="a in actions" :key="a.name" class="action" @submit.prevent="run(a)">
            <select v-if="a.needs === 'technician_id'" v-model="inputs[a.name]">
                <option value="">Select technician…</option>
                <option v-for="t in users.forRole('technician')" :key="t.id" :value="t.id">
                    {{ t.name }}
                </option>
            </select>
            <input v-else-if="a.needs" v-model="inputs[a.name]" :placeholder="placeholderFor(a)" />
            <button :disabled="submitting !== null">
                {{ submitting === a.name ? a.label + 'ing…' : a.label }}
            </button>
        </form>
    </div>
    <p v-else>No actions available for your role in this state.</p>
    <p v-if="error" class="error">{{ error }}</p>
</template>

<style scoped>
.actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
}

.action {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}
</style>
