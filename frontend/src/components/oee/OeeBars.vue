<script setup lang="ts">
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
    Chart,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    type ChartData,
    type ChartOptions,
} from 'chart.js'
import type { OeeResult } from '@/types/oee'

Chart.register(BarElement, CategoryScale, LinearScale, Tooltip)

const props = defineProps<{ result: OeeResult }>()

const BAR_COLOR = '#2563eb'

function toPercent(ratio: number): number {
    return Math.round(ratio * 1000) / 10
}

function percentLabel(ratio: number): string {
    return `${toPercent(ratio).toFixed(1)}%`
}

const components = computed(() => [
    { label: 'Availability', ratio: props.result.availability },
    { label: 'Performance', ratio: props.result.performance },
    { label: 'Quality', ratio: props.result.quality },
])

const barData = computed<ChartData<'bar'>>(() => ({
    labels: components.value.map((c) => c.label),
    datasets: [
        {
            data: components.value.map((c) => toPercent(c.ratio)),
            backgroundColor: BAR_COLOR,
            borderRadius: 4,
            barThickness: 22,
        },
    ],
}))

const barOptions: ChartOptions<'bar'> = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 300 },
    scales: {
        x: {
            min: 0,
            max: 100,
            ticks: { stepSize: 25, callback: (v) => `${v}%` },
            grid: { display: false },
        },
        y: {
            grid: { display: false },
        },
    },
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx) => `${ctx.formattedValue}%`,
            },
        },
    },
}
</script>

<template>
    <div class="oee-bars">
        <div class="oee-bars__canvas">
            <Bar :data="barData" :options="barOptions" />
        </div>
        <ul class="oee-bars__caption">
            <li v-for="c in components" :key="c.label">
                <span>{{ c.label }}</span>
                <strong>{{ percentLabel(c.ratio) }}</strong>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.oee-bars {
    flex: 1 1 260px;
    min-width: 240px;
}

.oee-bars__canvas {
    height: 130px;
}

.oee-bars__caption {
    display: flex;
    justify-content: space-between;
    list-style: none;
    padding: 0;
    margin: 0.5rem 0 0;
    color: var(--muted);
    font-size: 0.85rem;
}

.oee-bars__caption li {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.oee-bars__caption strong {
    color: var(--ink);
    font-size: 0.95rem;
}
</style>
