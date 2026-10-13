<script setup lang="ts">
import { computed } from 'vue'
import type { PaginationMeta } from '@/types/api'

const props = defineProps<{
  meta: PaginationMeta
  perPageOptions: number[]
  itemLabel: string
}>()

const emit = defineEmits<{ page: [page: number]; perPage: [perPage: number] }>()

const summary = computed(() =>
  props.meta.total === 0 ? '' : `${props.meta.from}–${props.meta.to} de ${props.meta.total} ${props.itemLabel}`,
)
</script>

<template>
  <div class="d-flex flex-column flex-sm-row align-center justify-space-between ga-3 mt-6">
    <span class="text-body-2 text-secondary">{{ summary }}</span>

    <v-pagination
      v-if="meta.last_page > 1"
      :model-value="meta.current_page"
      :length="meta.last_page"
      :total-visible="5"
      density="comfortable"
      rounded="sm"
      @update:model-value="emit('page', $event)"
    />

    <v-select
      :model-value="meta.per_page"
      :items="perPageOptions"
      label="Por página"
      density="compact"
      hide-details
      max-width="130"
      @update:model-value="emit('perPage', $event)"
    />
  </div>
</template>
