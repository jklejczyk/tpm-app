import { ref } from 'vue'
import { defineStore } from 'pinia'
import { fetchMachines } from '@/api/machines'
import { friendlyMessage } from '@/api/http'
import type { Machine } from '@/types/machine'

export const useMachinesStore = defineStore('machines', () => {
    const list = ref<Machine[]>([])
    const error = ref<string | null>(null)

    async function load(): Promise<void> {
        if (list.value.length > 0) {
            return
        }
        try {
            list.value = await fetchMachines()
        } catch (e) {
            error.value = friendlyMessage(e)
        }
    }

    return { list, error, load }
})
