<script setup lang="ts">
import { LIFECYCLE } from '@/constants/workOrder'
</script>

<template>
    <div class="detail-skeleton" aria-hidden="true">
        <span class="skeleton skeleton-title"></span>
        <span class="skeleton skeleton-pill"></span>
        <span class="skeleton skeleton-line"></span>
        <div class="skeleton-lifecycle">
            <span v-for="n in LIFECYCLE.length" :key="n" class="skeleton skeleton-step"></span>
        </div>
        <span class="skeleton skeleton-subtitle"></span>
        <div class="skeleton-actions">
            <span class="skeleton skeleton-btn"></span>
            <span class="skeleton skeleton-btn"></span>
        </div>
    </div>
</template>

<style scoped>
.detail-skeleton {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
    margin-top: 1rem;
    align-items: flex-start;
}

.skeleton-title {
    width: 22rem;
    max-width: 80%;
    height: 1.6rem;
}

.skeleton-pill {
    width: 6rem;
    height: 1.4rem;
    border-radius: 999px;
}

.skeleton-line {
    width: 28rem;
    max-width: 90%;
    height: 1rem;
}

.skeleton-subtitle {
    width: 8rem;
    height: 1.2rem;
    margin-top: 0.5rem;
}

.skeleton-lifecycle {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.skeleton-step {
    width: 5rem;
    height: 1.8rem;
    border-radius: 6px;
}

.skeleton-actions {
    display: flex;
    gap: 0.5rem;
}

.skeleton-btn {
    width: 6rem;
    height: 2.2rem;
    border-radius: 6px;
}
</style>
