<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useOeeStore } from '@/stores/oee'
import { useMachinesStore } from '@/stores/machines'
import { friendlyMessage } from '@/api/http'

const router = useRouter()

const store = useOeeStore()
const machines = useMachinesStore()

const machineId = ref('')
const periodStart = ref('2026-02-01T08:00')
const periodEnd = ref('2026-02-01T16:00')
const producedUnits = ref(900)
const defectiveUnits = ref(30)
const idealCycleTime = ref(30)

const error = ref<string | null>(null)
const submitting = ref(false)

onMounted(async () => {
    await machines.load()
    machineId.value = machines.list[0]?.id ?? ''
})

async function submit(): Promise<void> {
    if (submitting.value) {
        return
    }
    error.value = null
    submitting.value = true
    try {
        const created = await store.createRecord({
            machineId: machineId.value,
            periodStart: periodStart.value,
            periodEnd: periodEnd.value,
            producedUnits: producedUnits.value,
            defectiveUnits: defectiveUnits.value,
            idealCycleTime: idealCycleTime.value,
        })
        await router.push({
            name: 'oee',
            query: {
                machine: created.machineId,
                from: created.periodStart,
                to: created.periodEnd,
            },
        })
    } catch (e) {
        error.value = friendlyMessage(e)
    } finally {
        submitting.value = false
    }
}
</script>

<template>
    <section class="production">
        <h1>Record shift production</h1>
        <p class="production__intro">
            Enter what a machine produced during one shift. These figures are the basis for its OEE
            — downtime from work orders is combined with them to compute Availability, Performance
            and Quality.
        </p>

        <form class="production__form" @submit.prevent="submit">
            <label>
                Machine
                <select v-model="machineId" required>
                    <option v-for="machine in machines.list" :key="machine.id" :value="machine.id">
                        {{ machine.name }}
                    </option>
                </select>
                <small class="hint">Which machine this shift is for.</small>
            </label>

            <label>
                Shift start
                <input v-model="periodStart" type="datetime-local" required />
                <small class="hint"
                    >When the shift began — the start of the planned production window.</small
                >
            </label>

            <label>
                Shift end
                <input v-model="periodEnd" type="datetime-local" required />
                <small class="hint">
                    When the shift ended. The start–end length is the planned production time — the
                    OEE denominator.
                </small>
            </label>

            <label>
                Produced units
                <input v-model.number="producedUnits" type="number" min="0" required />
                <small class="hint"
                    >Total pieces made during the shift — good and defective together.</small
                >
            </label>

            <label>
                Defective units
                <input v-model.number="defectiveUnits" type="number" min="0" required />
                <small class="hint"
                    >How many of those were scrap/rejects. Good units = produced − defective (feeds
                    Quality).</small
                >
            </label>

            <label>
                Ideal cycle time (s/unit)
                <input v-model.number="idealCycleTime" type="number" min="1" required />
                <small class="hint"
                    >Ideal seconds to make one piece at full speed — the machine's rated pace (feeds
                    Performance).</small
                >
            </label>

            <button type="submit" :disabled="submitting">
                {{ submitting ? 'Saving…' : 'Save production record' }}
            </button>
        </form>

        <p v-if="error" class="error">{{ error }}</p>
    </section>
</template>

<style scoped>
.production {
    max-width: 480px;
}

.production__intro {
    color: var(--muted);
    margin-top: 0.5rem;
    max-width: 46ch;
}

.production__form {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin-top: 1rem;
}

.hint {
    display: block;
    color: var(--muted);
    font-size: 0.8rem;
    margin-top: 0.2rem;
}
</style>
