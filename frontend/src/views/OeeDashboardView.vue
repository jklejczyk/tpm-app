<script setup lang="ts">
import { nextTick, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useOeeStore } from '@/stores/oee'
import { useMachinesStore } from '@/stores/machines'
import OeeCharts from '@/components/oee/OeeCharts.vue'
import MachineWindowPicker from '@/components/oee/MachineWindowPicker.vue'
import type { ProductionRecordOption } from '@/types/oee'

const store = useOeeStore()
const machines = useMachinesStore()
const route = useRoute()

const selectedMachineId = ref('')
const selected = ref<ProductionRecordOption | null>(null)

onMounted(async () => {
    await Promise.all([machines.load(), store.loadRecords()])

    const machineParam = route.query.machine
    const fromParam = route.query.from
    const toParam = route.query.to
    if (typeof machineParam === 'string') {
        selectedMachineId.value = machineParam
        await nextTick()
        const match = store.records.find(
            (record) =>
                record.machineId === machineParam &&
                record.periodStart === fromParam &&
                record.periodEnd === toParam,
        )
        if (match) {
            selected.value = match
            await submit()
        }
        return
    }

    selectedMachineId.value = machines.list[0]?.id ?? ''
})

async function submit(): Promise<void> {
    if (store.loading || selected.value === null) {
        return
    }
    await store.load(selected.value.machineId, selected.value.periodStart, selected.value.periodEnd)
}
</script>

<template>
    <section class="oee">
        <h1>OEE</h1>

        <form class="oee__form" @submit.prevent="submit">
            <MachineWindowPicker
                v-model:machine-id="selectedMachineId"
                v-model:selected="selected"
            />
            <button type="submit" :disabled="store.loading || selected === null">
                {{ store.loading ? '…' : 'Calculate' }}
            </button>
        </form>

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
</style>
