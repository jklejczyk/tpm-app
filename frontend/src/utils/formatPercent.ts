export function toPercent(ratio: number): number {
    return Math.round(ratio * 1000) / 10
}

export function percentLabel(ratio: number): string {
    return `${toPercent(ratio).toFixed(1)}%`
}
