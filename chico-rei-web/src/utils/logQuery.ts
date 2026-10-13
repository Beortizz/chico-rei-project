import type { LocationQuery, LocationQueryRaw } from 'vue-router'

export const LOG_ACTION_OPTIONS = [
  { title: 'Todas as ações', value: '' },
  { title: 'Criação', value: 'created' },
  { title: 'Atualização', value: 'updated' },
  { title: 'Exclusão', value: 'deleted' },
]

export const LOG_PER_PAGE_OPTIONS = [15, 30, 50]
export const DEFAULT_LOG_PER_PAGE = 15

export interface LogFiltersState {
  name: string
  action: string
  per_page: number
  page: number
}

function first(value: LocationQuery[string] | undefined): string {
  const single = Array.isArray(value) ? value[0] : value

  return single ?? ''
}

function positiveInt(value: string, fallback: number): number {
  const parsed = Number.parseInt(value, 10)

  return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback
}

export function parseLogQuery(query: LocationQuery): LogFiltersState {
  const perPage = positiveInt(first(query.per_page), DEFAULT_LOG_PER_PAGE)
  const action = first(query.action)

  return {
    name: first(query.name),
    action: LOG_ACTION_OPTIONS.some((option) => option.value === action) ? action : '',
    per_page: LOG_PER_PAGE_OPTIONS.includes(perPage) ? perPage : DEFAULT_LOG_PER_PAGE,
    page: positiveInt(first(query.page), 1),
  }
}

export function toLogRouteQuery(state: LogFiltersState): LocationQueryRaw {
  const query: LocationQueryRaw = {}

  if (state.name) query.name = state.name
  if (state.action) query.action = state.action
  if (state.per_page !== DEFAULT_LOG_PER_PAGE) query.per_page = String(state.per_page)
  if (state.page > 1) query.page = String(state.page)

  return query
}

export function toLogApiParams(state: LogFiltersState): Record<string, string | number> {
  const params: Record<string, string | number> = { page: state.page, per_page: state.per_page }

  if (state.name) params.name = state.name
  if (state.action) params.action = state.action

  return params
}
