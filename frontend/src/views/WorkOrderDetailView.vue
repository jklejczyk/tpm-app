<script setup lang="ts">
import { computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useWorkOrdersStore } from '@/stores/workOrders'
import { useAuthStore } from '@/stores/auth'
import { REASON_LABEL } from '@/constants/workOrder'
import { allowedTransitions, type AllowedTransition } from '@/domain/transitions'
import StatusPill from '@/components/workOrders/StatusPill.vue'
import WorkOrderLifecycle from '@/components/workOrders/WorkOrderLifecycle.vue'
import WorkOrderActions from '@/components/workOrders/WorkOrderActions.vue'
import WorkOrderDetailSkeleton from '@/components/workOrders/WorkOrderDetailSkeleton.vue'

const props = defineProps<{ id: string }>()

const store = useWorkOrdersStore()
const auth = useAuthStore()
const router = useRouter()

watch(
    () => props.id,
    (id) => store.fetchOne(id),
    { immediate: true },
)

const actions = computed<AllowedTransition[]>(() => {
    const wo = store.current
    const user = auth.user
    if (!wo || !user) return []
    return allowedTransitions(wo, user.role, user.id)
})
</script>

<template>
    <button class="ghost" @click="router.push('/')">Return to work orders</button>

    <WorkOrderDetailSkeleton v-if="store.loading" />
    <p v-else-if="store.error" class="error">{{ store.error }}</p>

    <template v-else-if="store.current">
        <h2 class="mono">{{ store.current.id }}</h2>
        <p><StatusPill :status="store.current.status" /></p>
        <p class="mono">
            {{ store.current.machineName }} · {{ REASON_LABEL[store.current.reason] }} · reported by
            {{ store.current.reportedByName }}
        </p>

        <WorkOrderLifecycle :current="store.current.status" />

        <p v-if="store.current.assignedTo">
            Assigned to:
            <span class="mono">{{ store.current.assignedToName ?? store.current.assignedTo }}</span>
        </p>
        <p v-if="store.current.holdReason">Hold reason: {{ store.current.holdReason }}</p>
        <p v-if="store.current.resolution">Resolution: {{ store.current.resolution }}</p>

        <WorkOrderActions :id="store.current.id" :actions="actions" />
    </template>
</template>
