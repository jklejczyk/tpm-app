<script setup lang="ts">
import { computed } from 'vue'
import { Doughnut } from 'vue-chartjs'
import { Chart, ArcElement, Tooltip, type ChartData, type ChartOptions } from 'chart.js'
import type { OeeResult } from '@/types/oee'

Chart.register(ArcElement, Tooltip)

const props = defineProps<{ result: OeeResult }>()

const TRACK_COLOR = '#e2e5e9'
const GOOD_COLOR = '#16a34a'
const WARN_COLOR = '#ca8a04'
const BAD_COLOR = '#c0341d'

function toPercent(ratio: number): number {
    return Math.round(ratio * 1000) / 10
}

function percentLabel(ratio: number): string {
    return `${toPercent(ratio).toFixed(1)}%`
}

function oeeColor(ratio: number): string {
    if (ratio >= 0.85) {
        return GOOD_COLOR
    }
    if (ratio >= 0.6) {
        return WARN_COLOR
    }
    return BAD_COLOR
}

const oeePercentLabel = computed(() => percentLabel(props.result.oee))

const doughnutData = computed<ChartData<'doughnut'>>(() => {
    const filled = toPercent(props.result.oee)
    return {
        labels: ['OEE', 'Remaining'],
        datasets: [
            {
                data: [filled, 100 - filled],
                backgroundColor: [oeeColor(props.result.oee), TRACK_COLOR],
                borderWidth: 0,
            },
        ],
    }
})

const doughnutOptions: ChartOptions<'doughnut'> = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '72%',
    circumference: 360,
    animation: { duration: 300 },
    plugins: {
        legend: { display: false },
        tooltip: { enabled: false },
    },
}
</script>

<template>
    <div class="oee-gauge">
        <div class="oee-gauge__canvas">
            <Doughnut :data="doughnutData" :options="doughnutOptions" />
            <div class="oee-gauge__label">
                <span class="oee-gauge__value">{{ oeePercentLabel }}</span>
                <span class="oee-gauge__caption">OEE</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.oee-gauge__canvas {
    position: relative;
    width: 180px;
    height: 180px;
}

.oee-gauge__label {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.oee-gauge__value {
    font-size: 1.6rem;
    font-weight: 700;
}

.oee-gauge__caption {
    color: var(--muted);
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
</style>
