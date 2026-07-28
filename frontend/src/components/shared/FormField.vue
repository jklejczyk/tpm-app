<script setup lang="ts">
defineProps<{
    label: string
    hint?: string
}>()
</script>

<template>
    <label>
        {{ label }}
        <slot />
        <small v-if="hint" class="hint">{{ hint }}</small>
    </label>
</template>

<style scoped>
.hint {
    display: block;
    color: var(--muted);
    font-size: 0.8rem;
    margin-top: 0.2rem;
}
</style>
