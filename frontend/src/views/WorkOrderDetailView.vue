<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useWorkOrdersStore } from '@/stores/workOrders'
import { useAuthStore } from '@/stores/auth'
import { LIFECYCLE, REASON_LABEL, STATUS_LABEL } from '@/constants/workOrder'
import { allowedTransitions, type AllowedTransition } from '@/domain/transitions'

const props = defineProps<{ id: string }>()

const store = useWorkOrdersStore()
const auth = useAuthStore()
const router = useRouter()

const actionError = ref<string | null>(null)
const inputs = ref<Record<string, string>>({})

onMounted(() => store.fetchOne(props.id))

const actions = computed<AllowedTransition[]>(() => {
    const wo = store.current
    const user = auth.user
    if (!wo || !user) return []
    return allowedTransitions(wo, user.role, user.id)
})

const PLACEHOLDER: Record<NonNullable<AllowedTransition['needs']>, string> = {
    technician_id: 'technician id (e.g. 2)',
    reason: 'hold reason',
    resolution: 'resolution',
}

function placeholderFor(action: AllowedTransition): string {
    return action.needs ? PLACEHOLDER[action.needs] : ''
}

async function run(action: AllowedTransition) {
    actionError.value = null
    const payload: Record<string, string> = {}

    if (action.needs) {
        const value = (inputs.value[action.name] ?? '').trim()
        if (!value) {
            actionError.value = `Enter a value before you ${action.label.toLowerCase()}.`
            return
        }
        payload[action.needs] = value
    }

    try {
        await store.applyTransition(props.id, action.name, payload)
        inputs.value[action.name] = ''
    } catch (e) {
        actionError.value = (e as Error).message
    }
}
</script>

<template>
    <button class="ghost" @click="router.push('/')">Return to work orders</button>

    <div v-if="store.loading" class="detail-skeleton" aria-hidden="true">
        <span class="skeleton skeleton-title"></span>
        <span class="skeleton skeleton-pill"></span>
        <span class="skeleton skeleton-line"></span>
        <div class="skeleton-lifecycle">
            <span v-for="n in LIFECYCLE.length" :key="n" class="skeleton skeleton-step"></span>
        </div>
        <span class="skeleton skeleton-subtitle"></span>
        <div class="skeleton-actions">
            <span class="skeleton skeleton-btn"></span>
            <span class="skeleton skeleton-btn"></span>
        </div>
    </div>
    <p v-else-if="store.error" class="error">{{ store.error }}</p>

    <template v-else-if="store.current">
        <h2 class="mono">{{ store.current.id }}</h2>
        <p>
            <span class="pill" :data-status="store.current.status">
                {{ STATUS_LABEL[store.current.status] }}
            </span>
        </p>
        <p class="mono">
            {{ store.current.machineId }} · {{ REASON_LABEL[store.current.reason] }} · reported by
            {{ store.current.reportedByName ?? store.current.reportedBy }}
        </p>

        <ol class="lifecycle">
            <li v-for="s in LIFECYCLE" :key="s" :class="{ active: s === store.current.status }">
                {{ STATUS_LABEL[s] }}
            </li>
        </ol>

        <p v-if="store.current.assignedTo">
            Assigned to:
            <span class="mono">{{ store.current.assignedToName ?? store.current.assignedTo }}</span>
        </p>
        <p v-if="store.current.holdReason">Hold reason: {{ store.current.holdReason }}</p>
        <p v-if="store.current.resolution">Resolution: {{ store.current.resolution }}</p>

        <h3>Actions</h3>
        <div v-if="actions.length" class="actions">
            <form v-for="a in actions" :key="a.name" class="action" @submit.prevent="run(a)">
                <input v-if="a.needs" v-model="inputs[a.name]" :placeholder="placeholderFor(a)" />
                <button>{{ a.label }}</button>
            </form>
        </div>
        <p v-else>No actions available for your role in this state.</p>
        <p v-if="actionError" class="error">{{ actionError }}</p>
    </template>
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

/* Loading skeleton mirroring the detail layout. */
.detail-skeleton {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
    margin-top: 1rem;
    align-items: flex-start;
}

.skeleton {
    display: inline-block;
    border-radius: 4px;
    background: linear-gradient(90deg, #e6e6e6 25%, #f0f0f0 50%, #e6e6e6 75%);
    background-size: 200% 100%;
    animation: skeleton-shimmer 1.2s ease-in-out infinite;
}

.skeleton-title {
    width: 22rem;
    max-width: 80%;
    height: 1.6rem;
}

.skeleton-pill {
    width: 6rem;
    height: 1.4rem;
    border-radius: 999px;
}

.skeleton-line {
    width: 28rem;
    max-width: 90%;
    height: 1rem;
}

.skeleton-subtitle {
    width: 8rem;
    height: 1.2rem;
    margin-top: 0.5rem;
}

.skeleton-lifecycle {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.skeleton-step {
    width: 5rem;
    height: 1.8rem;
    border-radius: 6px;
}

.skeleton-actions {
    display: flex;
    gap: 0.5rem;
}

.skeleton-btn {
    width: 6rem;
    height: 2.2rem;
    border-radius: 6px;
}

@keyframes skeleton-shimmer {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .skeleton {
        animation: none;
    }
}
</style>
