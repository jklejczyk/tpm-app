<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useOeeStore } from '@/stores/oee'
import { useMachinesStore } from '@/stores/machines'
import { formatDate } from '@/utils/formatDate'
import OeeCharts from '@/components/oee/OeeCharts.vue'
import type { ProductionRecordOption } from '@/types/oee'

const store = useOeeStore()
const machines = useMachinesStore()
const route = useRoute()

const selectedMachineId = ref('')
const selected = ref<ProductionRecordOption | null>(null)

const windowsForMachine = computed(() =>
    store.records.filter((record) => record.machineId === selectedMachineId.value),
)

onMounted(async () => {
    await Promise.all([machines.load(), store.loadRecords()])

    const machineParam = route.query.machine
    const fromParam = route.query.from
    const toParam = route.query.to
    if (typeof machineParam === 'string') {
        selectedMachineId.value = machineParam
        await nextTick()
        const match = windowsForMachine.value.find(
            (record) => record.periodStart === fromParam && record.periodEnd === toParam,
        )
        if (match) {
            selected.value = match
            await submit()
        }
        return
    }

    selectedMachineId.value = machines.list[0]?.id ?? ''
})

watch(selectedMachineId, () => {
    selected.value = windowsForMachine.value[0] ?? null
})

async function submit(): Promise<void> {
    if (store.loading || selected.value === null) {
        return
    }
    await store.load(selected.value.machineId, selected.value.periodStart, selected.value.periodEnd)
}

function optionLabel(record: ProductionRecordOption): string {
    return `${formatDate(record.periodStart)} – ${formatDate(record.periodEnd)}`
}
</script>

<template>
    <section class="oee">
        <h1>OEE</h1>

        <form class="oee__form" @submit.prevent="submit">
            <label>
                Machine
                <select v-model="selectedMachineId" required>
                    <option v-for="machine in machines.list" :key="machine.id" :value="machine.id">
                        {{ machine.name }}
                    </option>
                </select>
            </label>
            <label>
                Production window
                <select v-model="selected" :disabled="windowsForMachine.length === 0" required>
                    <option
                        v-for="record in windowsForMachine"
                        :key="`${record.machineId}-${record.periodStart}`"
                        :value="record"
                    >
                        {{ optionLabel(record) }}
                    </option>
                </select>
            </label>
            <button type="submit" :disabled="store.loading || selected === null">
                {{ store.loading ? '…' : 'Calculate' }}
            </button>
        </form>

        <p v-if="windowsForMachine.length === 0" class="oee__empty">
            No production windows recorded for this machine.
        </p>

        <p v-if="store.error" class="error">{{ store.error }}</p>

        <div v-if="store.result" class="oee__result">
            <OeeCharts :result="store.result" />
            <p class="oee__raw">
                Produced {{ store.result.producedUnits }} · Defective
                {{ store.result.defectiveUnits }} · Downtime
                {{ Math.round(store.result.downtimeSeconds / 60) }} min
            </p>
        </div>
    </section>
</template>

<style scoped>
.oee {
    max-width: 760px;
}

.oee__form {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin-top: 1rem;
}

.oee__result {
    margin-top: 1.5rem;
}

.oee__raw {
    color: var(--muted);
    font-size: 0.85rem;
}

.oee__empty {
    color: var(--muted);
    font-size: 0.85rem;
}
</style>
