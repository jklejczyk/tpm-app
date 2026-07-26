import { ref } from 'vue'
import { defineStore } from 'pinia'
import { listUsers } from '@/api/users'
import type { Role, UserSummary } from '@/types/user'

export const useUsersStore = defineStore('users', () => {
    const byRole = ref<Partial<Record<Role, UserSummary[]>>>({})

    async function fetch(role: Role): Promise<void> {
        if (byRole.value[role]) return
        try {
            byRole.value[role] = await listUsers(role)
        } catch {
            // Reference data for a dropdown — degrade to an empty list, retry on next open.
        }
    }

    function forRole(role: Role): UserSummary[] {
        return byRole.value[role] ?? []
    }

    return { byRole, fetch, forRole }
})
