<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useWorkOrdersStore } from '@/stores/workOrders'
import { useMachinesStore } from '@/stores/machines'
import { REASON_LABEL } from '@/constants/workOrder'
import type { WorkOrderReason } from '@/types/workOrder'
import { friendlyMessage } from '@/api/http'

const store = useWorkOrdersStore()
const machines = useMachinesStore()

const machineId = ref('')
const reason = ref<WorkOrderReason>('breakdown')
const error = ref<string | null>(null)
const submitting = ref(false)

onMounted(async () => {
    await machines.load()
    machineId.value = machines.list[0]?.id ?? ''
})

async function submit() {
    error.value = null
    submitting.value = true
    try {
        await store.report(machineId.value, reason.value)
    } catch (e) {
        error.value = friendlyMessage(e)
    } finally {
        submitting.value = false
    }
}
</script>

<template>
    <form class="toolbar" @submit.prevent="submit">
        <select v-model="machineId" required>
            <option v-for="machine in machines.list" :key="machine.id" :value="machine.id">
                {{ machine.name }}
            </option>
        </select>
        <select v-model="reason">
            <option v-for="(label, value) in REASON_LABEL" :key="value" :value="value">
                {{ label }}
            </option>
        </select>
        <button :disabled="submitting">{{ submitting ? 'Reporting…' : 'Report fault' }}</button>
    </form>
    <p v-if="error" class="error">{{ error }}</p>
</template>
