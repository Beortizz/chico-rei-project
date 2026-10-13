<script setup lang="ts">
import type { Product } from '@/types/api'
import ProductCard from './ProductCard.vue'
import StateMessage from './StateMessage.vue'

defineProps<{
  products: Product[]
  loading: boolean
  error: string | null
  canManage: boolean
  skeletonCount: number
}>()

defineEmits<{ retry: []; clearFilters: []; delete: [product: Product] }>()
</script>

<template>
  <v-row v-if="loading" aria-busy="true" aria-label="Carregando produtos">
    <v-col v-for="n in skeletonCount" :key="n" cols="6" sm="4" lg="3">
      <v-skeleton-loader type="image, article" />
    </v-col>
  </v-row>

  <StateMessage v-else-if="error" icon="mdi-alert-circle-outline" title="Não foi possível carregar os produtos" :text="error">
    <v-btn color="primary" variant="flat" prepend-icon="mdi-refresh" @click="$emit('retry')">Tentar novamente</v-btn>
  </StateMessage>

  <StateMessage
    v-else-if="products.length === 0"
    icon="mdi-magnify-close"
    title="Nenhum produto encontrado"
    text="Tente ajustar ou limpar os filtros para ver mais resultados."
  >
    <v-btn variant="outlined" @click="$emit('clearFilters')">Limpar filtros</v-btn>
  </StateMessage>

  <v-row v-else>
    <v-col v-for="product in products" :key="product.id" cols="6" sm="4" lg="3">
      <ProductCard :product="product" :can-manage="canManage" @delete="$emit('delete', $event)" />
    </v-col>
  </v-row>
</template>
