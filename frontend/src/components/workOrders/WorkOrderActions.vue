<script setup lang="ts">
import { ref } from 'vue'
import { useWorkOrdersStore } from '@/stores/workOrders'
import type { AllowedTransition } from '@/domain/transitions'

const props = defineProps<{ id: string; actions: AllowedTransition[] }>()

const store = useWorkOrdersStore()

const error = ref<string | null>(null)
const inputs = ref<Record<string, string>>({})

const PLACEHOLDER: Record<NonNullable<AllowedTransition['needs']>, string> = {
    technician_id: 'technician id (e.g. 2)',
    reason: 'hold reason',
    resolution: 'resolution',
}

function placeholderFor(action: AllowedTransition): string {
    return action.needs ? PLACEHOLDER[action.needs] : ''
}

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

    try {
        await store.applyTransition(props.id, action.name, payload)
        inputs.value[action.name] = ''
    } catch (e) {
        error.value = (e as Error).message
    }
}
</script>

<template>
    <h3>Actions</h3>
    <div v-if="actions.length" class="actions">
        <form v-for="a in actions" :key="a.name" class="action" @submit.prevent="run(a)">
            <input v-if="a.needs" v-model="inputs[a.name]" :placeholder="placeholderFor(a)" />
            <button>{{ a.label }}</button>
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
