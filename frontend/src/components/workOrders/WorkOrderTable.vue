<script setup lang="ts">
import StatusPill from '@/components/workOrders/StatusPill.vue'
import { REASON_LABEL } from '@/constants/workOrder'
import { formatDate } from '@/utils/formatDate'
import type { SortDirection, WorkOrder, WorkOrderSortField } from '@/types/workOrder'

const props = defineProps<{
    orders: WorkOrder[]
    loading: boolean
    total: number
    sort: WorkOrderSortField
    direction: SortDirection
}>()

const emit = defineEmits<{
    sort: [field: WorkOrderSortField]
    open: [id: string]
}>()

const SKELETON_ROWS = 10
const COLUMNS = 6

function sortIndicator(field: WorkOrderSortField): string {
    if (props.sort !== field) return ''
    return props.direction === 'asc' ? ' ▲' : ' ▼'
}
</script>

<template>
    <table>
        <thead>
            <tr>
                <th class="sortable" @click="emit('sort', 'status')">
                    Status{{ sortIndicator('status') }}
                </th>
                <th>ID</th>
                <th class="sortable" @click="emit('sort', 'machine_id')">
                    Machine{{ sortIndicator('machine_id') }}
                </th>
                <th class="sortable" @click="emit('sort', 'reason')">
                    Reason{{ sortIndicator('reason') }}
                </th>
                <th class="sortable" @click="emit('sort', 'assigned_to')">
                    Assigned to{{ sortIndicator('assigned_to') }}
                </th>
                <th class="sortable" @click="emit('sort', 'reported_at')">
                    Reported{{ sortIndicator('reported_at') }}
                </th>
            </tr>
        </thead>

        <tbody v-if="loading" aria-hidden="true">
            <tr v-for="n in SKELETON_ROWS" :key="`skeleton-${n}`" class="skeleton-row">
                <td v-for="c in COLUMNS" :key="c"><span class="skeleton"></span></td>
            </tr>
        </tbody>

        <tbody v-else-if="total === 0">
            <tr>
                <td :colspan="COLUMNS" class="empty">
                    No work orders. Report the first fault above.
                </td>
            </tr>
        </tbody>

        <tbody v-else>
            <tr v-for="wo in orders" :key="wo.id" @click="emit('open', wo.id)">
                <td><StatusPill :status="wo.status" /></td>
                <td class="mono">{{ wo.id }}</td>
                <td class="mono">{{ wo.machineId }}</td>
                <td>{{ REASON_LABEL[wo.reason] }}</td>
                <td class="mono">{{ wo.assignedToName ?? wo.assignedTo ?? '—' }}</td>
                <td>{{ formatDate(wo.reportedAt) }}</td>
            </tr>
        </tbody>
    </table>
</template>

<style scoped>
.sortable {
    cursor: pointer;
    user-select: none;
}

.empty {
    text-align: center;
    padding: 1.5rem;
    opacity: 0.7;
}

.skeleton-row {
    cursor: default;
}

.skeleton {
    width: 70%;
    height: 0.85em;
}

.skeleton-row td:first-child .skeleton {
    width: 4rem;
    height: 1.1em;
    border-radius: 999px;
}
</style>
