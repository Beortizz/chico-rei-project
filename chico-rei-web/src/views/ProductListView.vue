<script setup lang="ts">
import { ref } from 'vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import ListPagination from '@/components/ListPagination.vue'
import ProductFilters from '@/components/ProductFilters.vue'
import ProductGrid from '@/components/ProductGrid.vue'
import { useRouteQueryList } from '@/composables/useRouteQueryList'
import { deleteProduct, listProducts } from '@/services/products'
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'
import type { Product } from '@/types/api'
import { normalizeError } from '@/utils/errors'
import { PER_PAGE_OPTIONS, SORT_OPTIONS, parseProductQuery, toApiParams, toRouteQuery } from '@/utils/productQuery'

const auth = useAuthStore()
const notifications = useNotificationsStore()

const { items: products, meta, loading, error, state, apply, clearFilters, reload } = useRouteQueryList({
  routeName: 'products',
  parse: parseProductQuery,
  toRouteQuery,
  toApiParams,
  fetch: listProducts,
})

const productToDelete = ref<Product | null>(null)
const deleting = ref(false)

async function confirmDelete(): Promise<void> {
  if (!productToDelete.value) return

  deleting.value = true

  try {
    await deleteProduct(productToDelete.value.id)
    notifications.show('Produto excluído com sucesso.')
    productToDelete.value = null

    if (products.value.length === 1 && state.value.page > 1) {
      apply({ page: state.value.page - 1 })
    } else {
      await reload()
    }
  } catch (e) {
    notifications.show(normalizeError(e).message, 'error')
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <section>
    <div class="d-flex flex-column flex-sm-row align-sm-center justify-space-between ga-3 mb-6">
      <h1 class="page-title">Produtos</h1>
      <v-btn v-if="auth.isAuthenticated" :to="{ name: 'product-create' }" color="primary" variant="flat" prepend-icon="mdi-plus">
        Novo produto
      </v-btn>
    </div>

    <ProductFilters :model-value="state" @apply="apply" @clear="clearFilters" />

    <div class="d-flex align-center justify-space-between ga-3 my-5">
      <span class="text-body-2 text-secondary" aria-live="polite">
        <template v-if="meta && !loading">{{ meta.total }} {{ meta.total === 1 ? 'produto' : 'produtos' }}</template>
      </span>
      <v-select
        :model-value="state.sort"
        :items="SORT_OPTIONS"
        label="Ordenar por"
        density="compact"
        hide-details
        style="max-width: 220px"
        @update:model-value="apply({ sort: $event })"
      />
    </div>

    <ProductGrid
      :products="products"
      :loading="loading"
      :error="error"
      :can-manage="auth.isAuthenticated"
      :skeleton-count="state.per_page"
      @retry="reload"
      @clear-filters="clearFilters"
      @delete="productToDelete = $event"
    />

    <ListPagination
      v-if="meta && !loading && !error"
      :meta="meta"
      :per-page-options="PER_PAGE_OPTIONS"
      item-label="produtos"
      @page="apply({ page: $event })"
      @per-page="apply({ per_page: $event })"
    />

    <ConfirmDialog
      :model-value="productToDelete !== null"
      title="Excluir produto"
      :text="`Tem certeza que deseja excluir “${productToDelete?.name}”? Esta ação não pode ser desfeita.`"
      confirm-label="Excluir"
      :loading="deleting"
      @update:model-value="productToDelete = null"
      @confirm="confirmDelete"
    />
  </section>
</template>
