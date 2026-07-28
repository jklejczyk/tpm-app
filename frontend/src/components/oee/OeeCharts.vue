<script setup lang="ts">
import { computed } from 'vue'
import { Doughnut, Bar } from 'vue-chartjs'
import {
    Chart,
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    type ChartData,
    type ChartOptions,
} from 'chart.js'
import type { OeeResult } from '@/types/oee'

Chart.register(ArcElement, BarElement, CategoryScale, LinearScale, Tooltip)

const props = defineProps<{ result: OeeResult }>()

const TRACK_COLOR = '#e2e5e9'
const GOOD_COLOR = '#16a34a'
const WARN_COLOR = '#ca8a04'
const BAD_COLOR = '#c0341d'
const BAR_COLOR = '#2563eb'

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
    <div class="oee-charts">
        <div class="oee-charts__gauge">
            <div class="oee-charts__gauge-canvas">
                <Doughnut :data="doughnutData" :options="doughnutOptions" />
                <div class="oee-charts__gauge-label">
                    <span class="oee-charts__gauge-value">{{ oeePercentLabel }}</span>
                    <span class="oee-charts__gauge-caption">OEE</span>
                </div>
            </div>
        </div>

        <div class="oee-charts__bars">
            <div class="oee-charts__bars-canvas">
                <Bar :data="barData" :options="barOptions" />
            </div>
            <ul class="oee-charts__bars-caption">
                <li v-for="c in components" :key="c.label">
                    <span>{{ c.label }}</span>
                    <strong>{{ percentLabel(c.ratio) }}</strong>
                </li>
            </ul>
        </div>
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

.oee-charts__gauge-canvas {
    position: relative;
    width: 180px;
    height: 180px;
}

.oee-charts__gauge-label {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.oee-charts__gauge-value {
    font-size: 1.6rem;
    font-weight: 700;
}

.oee-charts__gauge-caption {
    color: var(--muted);
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.oee-charts__bars {
    flex: 1 1 260px;
    min-width: 240px;
}

.oee-charts__bars-canvas {
    height: 130px;
}

.oee-charts__bars-caption {
    display: flex;
    justify-content: space-between;
    list-style: none;
    padding: 0;
    margin: 0.5rem 0 0;
    color: var(--muted);
    font-size: 0.85rem;
}

.oee-charts__bars-caption li {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.oee-charts__bars-caption strong {
    color: var(--ink);
    font-size: 0.95rem;
}
</style>
