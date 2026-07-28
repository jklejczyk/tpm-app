import { http } from './http'
import type { Wrapped } from '@/types/api'
import type { Machine } from '@/types/machine'

export async function fetchMachines(): Promise<Machine[]> {
    const { data } = await http.get<Wrapped<Machine[]>>('/machines')
    return data.data
}
