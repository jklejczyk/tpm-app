import { ref } from 'vue'
import { defineStore } from 'pinia'
import { createProductionRecord, fetchOee, fetchProductionRecords } from '@/api/oee'
import { ApiError, friendlyMessage } from '@/api/http'
import type { NewProductionRecord, OeeResult, ProductionRecordOption } from '@/types/oee'

export const useOeeStore = defineStore('oee', () => {
    const result = ref<OeeResult | null>(null)
    const loading = ref(false)
    const error = ref<string | null>(null)
    const records = ref<ProductionRecordOption[]>([])

    async function load(machineId: string, from: string, to: string): Promise<void> {
        loading.value = true
        error.value = null
        try {
            result.value = await fetchOee(machineId, from, to)
        } catch (e) {
            error.value =
                e instanceof ApiError && e.status === 404
                    ? 'No OEE data recorded for this machine and time window.'
                    : friendlyMessage(e)
            result.value = null
        } finally {
            loading.value = false
        }
    }

    async function loadRecords(): Promise<void> {
        try {
            records.value = await fetchProductionRecords()
        } catch (e) {
            error.value = friendlyMessage(e)
        }
    }

    async function createRecord(input: NewProductionRecord): Promise<ProductionRecordOption> {
        const created = await createProductionRecord(input)
        await loadRecords()
        return created
    }

    return { result, loading, error, records, load, loadRecords, createRecord }
})
