<script setup lang="ts">
import { imageUrlFromStoragePath } from '@/utils/format'

const props = defineProps<{ field: string; value: string; alt: string }>()

const EMPTY_VALUE = '—'
</script>

<template>
  <template v-if="props.field === 'image'">
    <v-img
      v-if="props.value !== EMPTY_VALUE"
      :src="imageUrlFromStoragePath(props.value)"
      :alt="props.alt"
      width="112"
      height="112"
      cover
      class="rounded bg-grey-lighten-3"
    >
      <template #error>
        <div class="d-flex flex-column align-center justify-center fill-height ga-1 pa-2 text-center text-grey-darken-3">
          <v-icon icon="mdi-image-off-outline" />
          <span class="text-body-small">Imagem indisponível</span>
        </div>
      </template>
    </v-img>
    <span v-else class="text-medium-emphasis">Sem imagem</span>
  </template>
  <span v-else-if="props.value === EMPTY_VALUE" class="text-medium-emphasis">{{ props.value }}</span>
  <span v-else class="text-pre-line text-break">{{ props.value }}</span>
</template>
