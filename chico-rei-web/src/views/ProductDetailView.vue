<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import StateMessage from '@/components/StateMessage.vue'
import { deleteProduct, getProduct } from '@/services/products'
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'
import type { Product } from '@/types/api'
import { isCanceled, normalizeError, type NormalizedError } from '@/utils/errors'
import { formatDateTime, formatPrice } from '@/utils/format'

const props = defineProps<{ id: string }>()

const auth = useAuthStore()
const notifications = useNotificationsStore()
const router = useRouter()

const product = ref<Product | null>(null)
const loading = ref(true)
const error = ref<NormalizedError | null>(null)
const confirmingDelete = ref(false)
const deleting = ref(false)

let controller: AbortController | null = null

async function load(): Promise<void> {
  controller?.abort()
  const current = new AbortController()
  controller = current

  loading.value = true
  error.value = null

  try {
    product.value = await getProduct(props.id, current.signal)
  } catch (e) {
    if (isCanceled(e)) return

    product.value = null
    error.value = normalizeError(e)
  } finally {
    if (!current.signal.aborted) loading.value = false
  }
}

async function remove(): Promise<void> {
  deleting.value = true

  try {
    await deleteProduct(props.id)
    notifications.show('Produto excluído com sucesso.')
    router.replace({ name: 'products' })
  } catch (e) {
    notifications.show(normalizeError(e).message, 'error')
    confirmingDelete.value = false
  } finally {
    deleting.value = false
  }
}

watch(() => props.id, load, { immediate: true })
onBeforeUnmount(() => controller?.abort())
</script>

<template>
  <v-sheet tag="section" color="transparent">
    <v-btn :to="{ name: 'products' }" variant="text" prepend-icon="mdi-arrow-left" class="mb-4">Voltar para produtos</v-btn>

    <v-row v-if="loading" aria-busy="true" aria-label="Carregando produto">
      <v-col cols="12" md="6"><v-skeleton-loader type="image" /></v-col>
      <v-col cols="12" md="6"><v-skeleton-loader type="heading, paragraph, paragraph, button" /></v-col>
    </v-row>

    <StateMessage
      v-else-if="error"
      :icon="error.status === 404 ? 'mdi-package-variant-closed-remove' : 'mdi-alert-circle-outline'"
      :title="error.status === 404 ? 'Produto não encontrado' : 'Não foi possível carregar o produto'"
      :text="error.status === 404 ? 'Ele pode ter sido removido ou o endereço está incorreto.' : error.message"
    >
      <v-btn v-if="error.status !== 404" color="primary" variant="flat" prepend-icon="mdi-refresh" @click="load">Tentar novamente</v-btn>
      <v-btn v-else :to="{ name: 'products' }" color="primary" variant="flat">Ver todos os produtos</v-btn>
    </StateMessage>

    <v-row v-else-if="product">
      <v-col cols="12" md="6">
        <v-img v-if="product.image" :src="product.image" :alt="product.name" :aspect-ratio="1" cover class="rounded-lg bg-grey-lighten-4" />
        <div v-else class="d-flex align-center justify-center bg-grey-lighten-4 rounded-lg" style="aspect-ratio: 1">
          <v-icon icon="mdi-image-off-outline" size="72" color="grey" />
        </div>
      </v-col>

      <v-col cols="12" md="6" class="d-flex flex-column ga-3">
        <span class="text-caption text-secondary text-uppercase">{{ product.category }}</span>
        <h1 class="page-title">{{ product.name }}</h1>
        <span class="text-h4 font-weight-bold">{{ formatPrice(product.price) }}</span>

        <div>
          <v-chip :color="product.in_stock ? 'success' : 'primary'" label size="small">
            {{ product.in_stock ? `Em estoque (${product.stock} un.)` : 'Esgotado' }}
          </v-chip>
        </div>

        <p v-if="product.description" class="text-body-1" style="white-space: pre-line">{{ product.description }}</p>
        <p v-else class="text-body-2 text-secondary">Este produto não possui descrição.</p>

        <p class="text-caption text-secondary mt-2">Atualizado em {{ formatDateTime(product.updated_at) }}</p>

        <div v-if="auth.isAuthenticated" class="d-flex flex-wrap ga-2 mt-4">
          <v-btn :to="{ name: 'product-edit', params: { id: product.id } }" color="primary" variant="flat" prepend-icon="mdi-pencil">Editar</v-btn>
          <v-btn color="error" variant="outlined" prepend-icon="mdi-delete-outline" @click="confirmingDelete = true">Excluir</v-btn>
        </div>
      </v-col>
    </v-row>

    <ConfirmDialog
      v-model="confirmingDelete"
      title="Excluir produto"
      :text="`Tem certeza que deseja excluir “${product?.name}”? Esta ação não pode ser desfeita.`"
      confirm-label="Excluir"
      :loading="deleting"
      @confirm="remove"
    />
  </v-sheet>
</template>
