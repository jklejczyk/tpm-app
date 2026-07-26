<script setup lang="ts">
defineProps<{ page: number; lastPage: number; total: number }>()

const emit = defineEmits<{ go: [page: number] }>()
</script>

<template>
    <nav class="pager">
        <button :disabled="page <= 1" @click="emit('go', page - 1)">‹ Prev</button>
        <span>Page {{ page }} of {{ lastPage }} · {{ total }} total</span>
        <button :disabled="page >= lastPage" @click="emit('go', page + 1)">Next ›</button>
    </nav>
</template>

<style scoped>
.pager {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-top: 1rem;
}

.pager button:disabled {
    opacity: 0.5;
    cursor: default;
}
</style>
