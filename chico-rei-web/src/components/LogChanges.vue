<script setup lang="ts">
import { computed } from 'vue'
import type { ProductLog } from '@/types/api'
import { formatPrice } from '@/utils/format'

const props = defineProps<{ log: ProductLog }>()

const FIELD_LABELS: Record<string, string> = {
  name: 'Nome',
  description: 'Descrição',
  image: 'Imagem',
  price: 'Preço',
  category: 'Categoria',
  stock: 'Estoque',
}

function display(field: string, value: unknown): string {
  if (value === null || value === undefined || value === '') return '—'
  if (field === 'price') return formatPrice(String(value))

  return String(value)
}

const changes = computed(() => {
  const next = props.log.new_values ?? {}
  const previous = props.log.old_values ?? {}

  return Object.keys(next)
    .filter((field) => field in FIELD_LABELS)
    .map((field) => ({ label: FIELD_LABELS[field], before: display(field, previous[field]), after: display(field, next[field]) }))
})

const snapshot = computed(() => {
  const values = props.log.action === 'deleted' ? props.log.old_values : props.log.new_values

  return Object.keys(FIELD_LABELS)
    .filter((field) => field !== 'description' && field !== 'image' && values && field in values)
    .map((field) => `${FIELD_LABELS[field]}: ${display(field, values?.[field])}`)
    .join(' · ')
})
</script>

<template>
  <ul v-if="log.action === 'updated'" class="pl-0 list-unstyled">
    <li v-for="change in changes" :key="change.label" class="text-body-2">
      <span class="font-weight-medium">{{ change.label }}:</span>
      <span class="text-secondary text-decoration-line-through mx-1">{{ change.before }}</span>
      <v-icon icon="mdi-arrow-right" size="x-small" />
      <span class="font-weight-medium ml-1">{{ change.after }}</span>
    </li>
  </ul>
  <span v-else class="text-body-2 text-secondary">{{ snapshot }}</span>
</template>
