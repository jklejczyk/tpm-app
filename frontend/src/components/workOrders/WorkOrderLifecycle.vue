<script setup lang="ts">
import { LIFECYCLE, STATUS_LABEL } from '@/constants/workOrder'
import type { WorkOrderStatus } from '@/types/workOrder'

defineProps<{ current: WorkOrderStatus }>()
</script>

<template>
    <ol class="lifecycle">
        <li v-for="s in LIFECYCLE" :key="s" :class="{ active: s === current }">
            {{ STATUS_LABEL[s] }}
        </li>
    </ol>
</template>
