<script setup lang="ts">
import { STATUS_LABEL } from '@/constants/workOrder'
import type { WorkOrderStatus } from '@/types/workOrder'

defineProps<{ status: WorkOrderStatus }>()
</script>

<template>
    <span class="pill" :data-status="status">{{ STATUS_LABEL[status] }}</span>
</template>
