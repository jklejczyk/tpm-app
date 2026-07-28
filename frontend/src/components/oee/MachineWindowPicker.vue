<script setup lang="ts">
import { computed, watch } from 'vue'
import { useOeeStore } from '@/stores/oee'
import { useMachinesStore } from '@/stores/machines'
import { formatDate } from '@/utils/formatDate'
import FormField from '@/components/shared/FormField.vue'
import type { ProductionRecordOption } from '@/types/oee'

const store = useOeeStore()
const machines = useMachinesStore()

const machineId = defineModel<string>('machineId', { required: true })
const selected = defineModel<ProductionRecordOption | null>('selected', { required: true })

const windowsForMachine = computed(() =>
    store.records.filter((record) => record.machineId === machineId.value),
)

watch(machineId, () => {
    selected.value = windowsForMachine.value[0] ?? null
})

function optionLabel(record: ProductionRecordOption): string {
    return `${formatDate(record.periodStart)} – ${formatDate(record.periodEnd)}`
}
</script>

<template>
    <FormField label="Machine">
        <select v-model="machineId" required>
            <option v-for="machine in machines.list" :key="machine.id" :value="machine.id">
                {{ machine.name }}
            </option>
        </select>
    </FormField>
    <FormField label="Production window">
        <select v-model="selected" :disabled="windowsForMachine.length === 0" required>
            <option
                v-for="record in windowsForMachine"
                :key="`${record.machineId}-${record.periodStart}`"
                :value="record"
            >
                {{ optionLabel(record) }}
            </option>
        </select>
    </FormField>
    <p v-if="windowsForMachine.length === 0" class="oee__empty">
        No production windows recorded for this machine.
    </p>
</template>

<style scoped>
.oee__empty {
    color: var(--muted);
    font-size: 0.85rem;
}
</style>
