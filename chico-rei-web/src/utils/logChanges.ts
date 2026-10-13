import type { ProductLog, ProductLogAction } from '@/types/api'
import { formatPrice } from '@/utils/format'

export const LOG_FIELD_LABELS: Record<string, string> = {
  name: 'Nome',
  description: 'Descrição',
  category: 'Categoria',
  price: 'Preço',
  stock: 'Estoque',
  image: 'Imagem',
}

export const LOG_ACTION_LABELS: Record<ProductLogAction, { text: string; color: string }> = {
  created: { text: 'Criação', color: 'success' },
  updated: { text: 'Atualização', color: 'info' },
  deleted: { text: 'Exclusão', color: 'error' },
}

export interface LogFieldChange {
  field: string
  label: string
  before: string
  after: string
}

export interface LogFieldValue {
  field: string
  label: string
  value: string
}

export function displayLogValue(field: string, value: unknown): string {
  if (value === null || value === undefined || value === '') return '—'
  if (field === 'price') return formatPrice(String(value))

  return String(value)
}

export function getLogChanges(log: ProductLog): LogFieldChange[] {
  const next = log.new_values ?? {}
  const previous = log.old_values ?? {}

  return Object.keys(LOG_FIELD_LABELS)
    .filter((field) => field in next)
    .map((field) => ({
      field,
      label: LOG_FIELD_LABELS[field] ?? field,
      before: displayLogValue(field, previous[field]),
      after: displayLogValue(field, next[field]),
    }))
}

export function getLogSnapshot(log: ProductLog): LogFieldValue[] {
  const values = log.action === 'deleted' ? log.old_values : log.new_values
  if (!values) return []

  return Object.keys(LOG_FIELD_LABELS)
    .filter((field) => field in values)
    .map((field) => ({ field, label: LOG_FIELD_LABELS[field] ?? field, value: displayLogValue(field, values[field]) }))
}
