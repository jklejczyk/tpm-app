export interface OeeResult {
    machineId: string
    periodStart: string
    periodEnd: string
    plannedSeconds: number
    downtimeSeconds: number
    producedUnits: number
    defectiveUnits: number
    availability: number
    performance: number
    quality: number
    oee: number
}

export interface ProductionRecordOption {
    machineId: string
    machineName: string
    periodStart: string
    periodEnd: string
}

export interface NewProductionRecord {
    machineId: string
    periodStart: string
    periodEnd: string
    producedUnits: number
    defectiveUnits: number
    idealCycleTime: number
}
