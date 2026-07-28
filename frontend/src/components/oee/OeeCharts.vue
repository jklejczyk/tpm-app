<script setup lang="ts">
import OeeGauge from '@/components/oee/OeeGauge.vue'
import OeeBars from '@/components/oee/OeeBars.vue'
import type { OeeResult } from '@/types/oee'

defineProps<{ result: OeeResult }>()
</script>

<template>
    <div class="oee-charts">
        <OeeGauge :result="result" />
        <OeeBars :result="result" />
    </div>
</template>

<style scoped>
.oee-charts {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 2rem;
    margin-top: 1rem;
}
</style>
