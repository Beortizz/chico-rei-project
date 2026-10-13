import type { LocationQuery, LocationQueryRaw } from 'vue-router'

export const PER_PAGE_OPTIONS = [12, 24, 48]
export const DEFAULT_PER_PAGE = 12

export const SORT_OPTIONS = [
  { title: 'Padrão', value: '' },
  { title: 'Menor preço', value: 'price:asc' },
  { title: 'Maior preço', value: 'price:desc' },
  { title: 'Nome (A-Z)', value: 'name:asc' },
  { title: 'Nome (Z-A)', value: 'name:desc' },
  { title: 'Maior estoque', value: 'stock:desc' },
  { title: 'Mais recentes', value: 'created_at:desc' },
]

export const STOCK_OPTIONS = [
  { title: 'Todos', value: '' },
  { title: 'Em estoque', value: '1' },
  { title: 'Sem estoque', value: '0' },
]

export interface ProductFiltersState {
  name: string
  category: string
  min_price: string
  max_price: string
  in_stock: string
  sort: string
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

/**
 * A query string é a fonte da verdade dos filtros: permite compartilhar a URL e usar o botão voltar.
 */
export function parseProductQuery(query: LocationQuery): ProductFiltersState {
  const perPage = positiveInt(first(query.per_page), DEFAULT_PER_PAGE)
  const sort = first(query.sort)

  return {
    name: first(query.name),
    category: first(query.category),
    min_price: first(query.min_price),
    max_price: first(query.max_price),
    in_stock: ['0', '1'].includes(first(query.in_stock)) ? first(query.in_stock) : '',
    sort: SORT_OPTIONS.some((option) => option.value === sort) ? sort : '',
    per_page: PER_PAGE_OPTIONS.includes(perPage) ? perPage : DEFAULT_PER_PAGE,
    page: positiveInt(first(query.page), 1),
  }
}

/**
 * Omite valores vazios e padrões para manter a URL curta.
 */
export function toRouteQuery(state: ProductFiltersState): LocationQueryRaw {
  const query: LocationQueryRaw = {}

  if (state.name) query.name = state.name
  if (state.category) query.category = state.category
  if (state.min_price) query.min_price = state.min_price
  if (state.max_price) query.max_price = state.max_price
  if (state.in_stock) query.in_stock = state.in_stock
  if (state.sort) query.sort = state.sort
  if (state.per_page !== DEFAULT_PER_PAGE) query.per_page = String(state.per_page)
  if (state.page > 1) query.page = String(state.page)

  return query
}

export function toApiParams(state: ProductFiltersState): Record<string, string | number> {
  const params: Record<string, string | number> = { page: state.page, per_page: state.per_page }
  const [sort, direction] = state.sort.split(':')

  if (state.name) params.name = state.name
  if (state.category) params.category = state.category
  if (state.min_price) params.min_price = state.min_price
  if (state.max_price) params.max_price = state.max_price
  if (state.in_stock) params.in_stock = state.in_stock
  if (sort && direction) {
    params.sort = sort
    params.direction = direction
  }

  return params
}
