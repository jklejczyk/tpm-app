import { http } from './http'
import type { Wrapped } from '@/types/api'
import type { NewProductionRecord, OeeResult, ProductionRecordOption } from '@/types/oee'

export async function fetchOee(machineId: string, from: string, to: string): Promise<OeeResult> {
    const { data } = await http.get<Wrapped<OeeResult>>(`/machines/${machineId}/oee`, {
        params: { from, to },
    })
    return data.data
}

export async function fetchProductionRecords(): Promise<ProductionRecordOption[]> {
    const { data } = await http.get<Wrapped<ProductionRecordOption[]>>('/production-records')
    return data.data
}

export async function createProductionRecord(
    input: NewProductionRecord,
): Promise<ProductionRecordOption> {
    const { data } = await http.post<Wrapped<ProductionRecordOption>>('/production-records', {
        machine_id: input.machineId,
        period_start: input.periodStart,
        period_end: input.periodEnd,
        produced_units: input.producedUnits,
        defective_units: input.defectiveUnits,
        ideal_cycle_time: input.idealCycleTime,
    })
    return data.data
}
