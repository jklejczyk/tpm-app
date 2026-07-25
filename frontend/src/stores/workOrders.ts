import { ref } from 'vue'
import { defineStore } from 'pinia'
import * as woApi from '@/api/workOrders'
import { friendlyMessage } from '@/api/http'
import type {
    SortDirection,
    TransitionName,
    WorkOrder,
    WorkOrderReason,
    WorkOrderSortField,
} from '@/types/workOrder'

export const useWorkOrdersStore = defineStore('workOrders', () => {
    const list = ref<WorkOrder[]>([])
    const current = ref<WorkOrder | null>(null)
    const loading = ref(false)
    const error = ref<string | null>(null)

    const page = ref(1)
    const lastPage = ref(1)
    const total = ref(0)
    const perPage = ref(10)
    const sort = ref<WorkOrderSortField>('reported_at')
    const direction = ref<SortDirection>('desc')

    async function fetchList(): Promise<void> {
        loading.value = true
        error.value = null
        try {
            const result = await woApi.listWorkOrders({
                page: page.value,
                perPage: perPage.value,
                sort: sort.value,
                direction: direction.value,
            })
            list.value = result.data
            page.value = result.meta.currentPage
            lastPage.value = result.meta.lastPage
            total.value = result.meta.total
            perPage.value = result.meta.perPage
        } catch (e) {
            error.value = friendlyMessage(e)
        } finally {
            loading.value = false
        }
    }

    function goToPage(target: number): void {
        if (target < 1 || target > lastPage.value || target === page.value) return
        page.value = target
        void fetchList()
    }

    function setSort(field: WorkOrderSortField): void {
        if (sort.value === field) {
            direction.value = direction.value === 'asc' ? 'desc' : 'asc'
        } else {
            sort.value = field
            direction.value = 'asc'
        }
        page.value = 1
        void fetchList()
    }

    async function fetchOne(id: string): Promise<void> {
        loading.value = true
        error.value = null
        current.value = null
        try {
            current.value = await woApi.getWorkOrder(id)
        } catch (e) {
            error.value = friendlyMessage(e)
        } finally {
            loading.value = false
        }
    }

    async function report(machineId: string, reason: WorkOrderReason): Promise<void> {
        await woApi.reportWorkOrder(machineId, reason)
        await fetchList()
    }

    async function applyTransition(
        id: string,
        name: TransitionName,
        payload: Record<string, string> = {},
    ): Promise<void> {
        const wo = await woApi.transitionWorkOrder(id, name, payload)
        current.value = wo
        const index = list.value.findIndex((w) => w.id === id)
        if (index !== -1) list.value[index] = wo
    }

    return {
        list,
        current,
        loading,
        error,
        page,
        lastPage,
        total,
        perPage,
        sort,
        direction,
        fetchList,
        goToPage,
        setSort,
        fetchOne,
        report,
        applyTransition,
    }
})
