<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'

const props = defineProps<{ currentUrl: string | null }>()

const file = defineModel<File | null>('file', { default: null })
const remove = defineModel<boolean>('remove', { default: false })

const MAX_SIZE_BYTES = 2 * 1024 * 1024
const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/webp']

const input = ref<HTMLInputElement | null>(null)
const error = ref('')
const previewUrl = ref<string | null>(null)

watch(file, (value) => {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)

  previewUrl.value = value ? URL.createObjectURL(value) : null
})

onBeforeUnmount(() => {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
})

const displayedImage = computed(() => previewUrl.value ?? (remove.value ? null : props.currentUrl))
const hasStoredImage = computed(() => props.currentUrl !== null)

function onPick(event: Event): void {
  const picked = (event.target as HTMLInputElement).files?.[0]

  if (!picked) return

  if (!ACCEPTED_TYPES.includes(picked.type)) {
    error.value = 'Use uma imagem JPG, PNG ou WebP.'
  } else if (picked.size > MAX_SIZE_BYTES) {
    error.value = 'A imagem deve ter no máximo 2 MB.'
  } else {
    error.value = ''
    file.value = picked
    remove.value = false
  }

  if (input.value) input.value.value = ''
}

function clearSelection(): void {
  file.value = null
  error.value = ''
}
</script>

<template>
  <div>
    <div class="text-body-medium font-weight-medium mb-2">Imagem</div>

    <div class="d-flex flex-column flex-sm-row ga-4 align-start">
      <v-sheet color="grey-lighten-4" rounded="lg" width="160" height="160" class="overflow-hidden flex-shrink-0">
        <v-img v-if="displayedImage" :src="displayedImage" alt="Pré-visualização da imagem do produto" cover height="160" />
        <div v-else class="d-flex align-center justify-center fill-height">
          <v-icon icon="mdi-image-outline" size="40" color="grey" />
        </div>
      </v-sheet>

      <div class="d-flex flex-column ga-2 align-start">
        <input ref="input" type="file" accept="image/jpeg,image/png,image/webp" hidden @change="onPick" />

        <v-btn variant="outlined" prepend-icon="mdi-upload" @click="input?.click()">
          {{ displayedImage ? 'Trocar imagem' : 'Escolher imagem' }}
        </v-btn>
        <v-btn v-if="file" variant="text" prepend-icon="mdi-close" @click="clearSelection">Cancelar seleção</v-btn>
        <v-btn
          v-else-if="hasStoredImage && !remove"
          variant="text"
          color="error"
          prepend-icon="mdi-delete-outline"
          @click="remove = true"
        >
          Remover imagem
        </v-btn>
        <v-btn v-else-if="hasStoredImage && remove" variant="text" prepend-icon="mdi-undo" @click="remove = false">
          Desfazer remoção
        </v-btn>

        <span class="text-body-small text-secondary">JPG, PNG ou WebP, até 2 MB.</span>
        <span v-if="error" class="text-body-small text-error" role="alert">{{ error }}</span>
      </div>
    </div>
  </div>
</template>
