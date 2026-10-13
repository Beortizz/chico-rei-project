import { computed, ref, watch, type Ref } from 'vue'
import { useRoute, useRouter, type LocationQuery, type LocationQueryRaw } from 'vue-router'
import { isCanceled, normalizeError } from '@/utils/errors'
import type { Paginated, PaginationMeta } from '@/types/api'

interface Options<T, S extends { page: number }> {
  routeName: string
  parse: (query: LocationQuery) => S
  toRouteQuery: (state: S) => LocationQueryRaw
  toApiParams: (state: S) => Record<string, string | number>
  fetch: (params: Record<string, string | number>, signal: AbortSignal) => Promise<Paginated<T>>
}

/**
 * Lista paginada cujo estado vive na query string. Cada mudança de URL dispara uma única requisição,
 * e a requisição anterior ainda em andamento é cancelada para evitar respostas fora de ordem.
 */
export function useRouteQueryList<T, S extends { page: number }>(options: Options<T, S>) {
  const route = useRoute()
  const router = useRouter()

  const items = ref([]) as Ref<T[]>
  const meta = ref<PaginationMeta | null>(null)
  const loading = ref(true)
  const error = ref<string | null>(null)

  const state = computed(() => options.parse(route.query))

  let controller: AbortController | null = null

  async function load(): Promise<void> {
    if (route.name !== options.routeName) return

    controller?.abort()
    const current = new AbortController()
    controller = current

    loading.value = true
    error.value = null

    try {
      const result = await options.fetch(options.toApiParams(state.value), current.signal)
      items.value = result.data
      meta.value = result.meta
    } catch (e) {
      if (isCanceled(e)) return

      error.value = normalizeError(e).message
    } finally {
      if (!current.signal.aborted) loading.value = false
    }
  }

  /**
   * Volta para a primeira página sempre que um filtro muda, a menos que a página seja informada.
   */
  function apply(changes: Partial<S>): void {
    const next: S = { ...state.value, ...changes }

    if (!('page' in changes)) next.page = 1

    router.push({ query: options.toRouteQuery(next) })
  }

  function clearFilters(): void {
    router.push({ query: {} })
  }

  watch(() => route.fullPath, load, { immediate: true })

  return { items, meta, loading, error, state, apply, clearFilters, reload: load }
}
