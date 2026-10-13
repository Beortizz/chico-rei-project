<script setup lang="ts">
import { computed } from 'vue'
import type { Product } from '@/types/api'
import { formatPrice } from '@/utils/format'

const props = defineProps<{
  product: Product
  canManage: boolean
}>()

defineEmits<{ delete: [product: Product] }>()

const lowStockThreshold = 5

const stockLabel = computed(() => {
  if (props.product.stock === 0) return 'Esgotado'
  if (props.product.stock <= lowStockThreshold) return `Últimas ${props.product.stock} unidades`

  return null
})
</script>

<template>
  <v-card :to="{ name: 'product-detail', params: { id: product.id } }" variant="flat" border class="h-100 d-flex flex-column">
    <div class="position-relative">
      <v-img v-if="product.image" :src="product.image" :alt="product.name" :aspect-ratio="1" cover class="bg-grey-lighten-4" />
      <v-responsive v-else :aspect-ratio="1" class="bg-grey-lighten-4">
        <div class="d-flex align-center justify-center fill-height">
          <v-icon icon="mdi-image-off-outline" size="48" color="grey" />
        </div>
      </v-responsive>

      <div v-if="stockLabel" class="position-absolute top-0 left-0 pa-2">
        <v-chip :color="product.stock === 0 ? 'primary' : 'accent'" size="small" label>
          {{ stockLabel }}
        </v-chip>
      </div>

      <div v-if="canManage" class="position-absolute top-0 right-0 pa-2 d-flex ga-1">
        <v-btn
          icon="mdi-pencil"
          size="small"
          color="white"
          aria-label="Editar produto"
          :to="{ name: 'product-edit', params: { id: product.id } }"
          @click.stop
        />
        <v-btn icon="mdi-delete" size="small" color="white" aria-label="Excluir produto" @click.prevent.stop="$emit('delete', product)" />
      </div>
    </div>

    <v-card-text class="d-flex flex-column flex-grow-1 ga-1">
      <span class="text-caption text-secondary text-uppercase">{{ product.category }}</span>
      <h3 class="text-subtitle-1 font-weight-bold text-clamp-2">{{ product.name }}</h3>
      <span class="text-h6 font-weight-bold mt-auto pt-2">{{ formatPrice(product.price) }}</span>
    </v-card-text>
  </v-card>
</template>
