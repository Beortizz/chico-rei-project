import { http } from './http'
import type { Paginated, ProductLog } from '@/types/api'

export async function listProductLogs(params: Record<string, string | number>, signal?: AbortSignal): Promise<Paginated<ProductLog>> {
  const { data } = await http.get<Paginated<ProductLog>>('/product-logs', { params, signal })

  return data
}
