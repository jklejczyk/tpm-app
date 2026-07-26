import { http } from './http'
import type { Wrapped } from '@/types/api'
import type {
    Paginated,
    SortDirection,
    TransitionName,
    WorkOrder,
    WorkOrderReason,
    WorkOrderSortField,
} from '@/types/workOrder'

export interface ListParams {
    page?: number
    perPage?: number
    sort?: WorkOrderSortField
    direction?: SortDirection
}

interface RawPaginated<T> {
    data: T
    meta: { current_page: number; last_page: number; per_page: number; total: number }
}

export async function listWorkOrders(params: ListParams = {}): Promise<Paginated<WorkOrder>> {
    const { data } = await http.get<RawPaginated<WorkOrder[]>>('/work-orders', {
        params: {
            page: params.page,
            per_page: params.perPage,
            sort: params.sort,
            direction: params.direction,
        },
    })

    return {
        data: data.data,
        meta: {
            currentPage: data.meta.current_page,
            lastPage: data.meta.last_page,
            perPage: data.meta.per_page,
            total: data.meta.total,
        },
    }
}

export async function getWorkOrder(id: string): Promise<WorkOrder> {
    const { data } = await http.get<Wrapped<WorkOrder>>(`/work-orders/${id}`)
    return data.data
}

export async function reportWorkOrder(
    machineId: string,
    reason: WorkOrderReason,
): Promise<WorkOrder> {
    const { data } = await http.post<Wrapped<WorkOrder>>('/work-orders', {
        machine_id: machineId,
        reason,
    })
    return data.data
}

export async function transitionWorkOrder(
    id: string,
    name: TransitionName,
    payload: Record<string, string> = {},
): Promise<WorkOrder> {
    const { data } = await http.post<Wrapped<WorkOrder>>(`/work-orders/${id}/${name}`, payload)
    return data.data
}
