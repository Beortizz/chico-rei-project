<script setup lang="ts">
import { computed } from 'vue'
import { useDisplay } from 'vuetify'
import type { ProductLog } from '@/types/api'
import { getLogChanges, getLogSnapshot } from '@/utils/logChanges'

const props = defineProps<{ log: ProductLog }>()

const { lgAndUp, mdAndUp } = useDisplay()
const maxWidth = computed(() => (lgAndUp.value ? 320 : mdAndUp.value ? 200 : 100))

/**
 * O resumo é uma linha truncada porque valores como a descrição podem ser arbitrariamente longos;
 * o conteúdo completo fica no modal de detalhes. A largura acompanha a tela para a tabela nunca rolar.
 */
const summary = computed(() => {
  if (props.log.action === 'updated') {
    return getLogChanges(props.log)
      .map((change) => (change.field === 'image'
        ? `${change.label}: ${change.after === '—' ? 'removida' : 'alterada'}`
        : `${change.label}: ${change.before} → ${change.after}`))
      .join(' · ')
  }

  return getLogSnapshot(props.log)
    .filter((item) => item.field !== 'description' && item.field !== 'image')
    .map((item) => `${item.label}: ${item.value}`)
    .join(' · ')
})
</script>

<template>
  <v-sheet color="transparent" :max-width="maxWidth" class="text-truncate text-body-medium text-secondary" :title="summary">
    {{ summary }}
  </v-sheet>
</template>
