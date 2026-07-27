<script setup lang="ts">
import { ref } from 'vue'
import { useWorkOrdersStore } from '@/stores/workOrders'
import type { WorkOrderReason } from '@/types/workOrder'

const store = useWorkOrdersStore()

const machineId = ref('')
const reason = ref<WorkOrderReason>('breakdown')
const error = ref<string | null>(null)
const submitting = ref(false)

async function submit() {
    error.value = null
    submitting.value = true
    try {
        await store.report(machineId.value, reason.value)
        machineId.value = ''
    } catch (e) {
        error.value = (e as Error).message
    } finally {
        submitting.value = false
    }
}
</script>

<template>
    <form class="toolbar" @submit.prevent="submit">
        <input v-model="machineId" placeholder="machine id (e.g. m-1)" />
        <select v-model="reason">
            <option value="breakdown">Breakdown</option>
            <option value="inspection">Inspection</option>
            <option value="operator_report">Operator report</option>
        </select>
        <button :disabled="submitting">{{ submitting ? 'Reporting…' : 'Report fault' }}</button>
    </form>
    <p v-if="error" class="error">{{ error }}</p>
</template>
