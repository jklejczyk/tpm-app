<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useWorkOrdersStore } from '@/stores/workOrders'
import ReportFaultForm from '@/components/workOrders/ReportFaultForm.vue'
import WorkOrderTable from '@/components/workOrders/WorkOrderTable.vue'
import BasePager from '@/components/shared/BasePager.vue'

const store = useWorkOrdersStore()
const router = useRouter()

onMounted(() => store.fetchList())

function open(id: string) {
    router.push({ name: 'work-order', params: { id } })
}
</script>

<template>
    <h2>Work orders</h2>

    <ReportFaultForm />

    <p v-if="store.error" class="error">{{ store.error }}</p>
    <template v-else>
        <div class="wide">
            <WorkOrderTable
                :orders="store.list"
                :loading="store.loading"
                :total="store.total"
                :sort="store.sort"
                :direction="store.direction"
                @sort="store.setSort"
                @open="open"
            />
            <BasePager
                v-if="!store.loading && store.total > 0"
                :page="store.page"
                :last-page="store.lastPage"
                :total="store.total"
                @go="store.goToPage"
            />
        </div>
    </template>
</template>

<style scoped>
.wide {
    margin-inline: calc(50% - 50vw);
    padding-inline: 1.25rem;
    overflow-x: auto;
}
</style>
