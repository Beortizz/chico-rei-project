<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import ProductForm from '@/components/ProductForm.vue'
import StateMessage from '@/components/StateMessage.vue'
import { createProduct, getProduct, updateProduct } from '@/services/products'
import { useNotificationsStore } from '@/stores/notifications'
import type { Product, ProductFormValues } from '@/types/api'
import { normalizeError, type NormalizedError } from '@/utils/errors'

const props = defineProps<{ id?: string }>()

const router = useRouter()
const notifications = useNotificationsStore()

const isEditing = computed(() => props.id !== undefined)
const product = ref<Product | null>(null)
const loading = ref(isEditing.value)
const loadError = ref<NormalizedError | null>(null)
const submitting = ref(false)
const serverErrors = ref<Record<string, string[]>>({})

async function loadProduct(): Promise<void> {
  if (!props.id) return

  loading.value = true
  loadError.value = null

  try {
    product.value = await getProduct(props.id)
  } catch (e) {
    loadError.value = normalizeError(e)
  } finally {
    loading.value = false
  }
}

async function save(values: ProductFormValues): Promise<void> {
  submitting.value = true
  serverErrors.value = {}

  try {
    const saved = props.id ? await updateProduct(props.id, values) : await createProduct(values)

    notifications.show(props.id ? 'Produto atualizado com sucesso.' : 'Produto criado com sucesso.')
    router.push({ name: 'product-detail', params: { id: saved.id } })
  } catch (e) {
    const error = normalizeError(e)
    serverErrors.value = error.fieldErrors
    notifications.show(error.message, 'error')
  } finally {
    submitting.value = false
  }
}

function cancel(): void {
  router.back()
}

onMounted(loadProduct)
</script>

<template>
  <v-sheet tag="section" color="transparent" max-width="760" class="mx-auto d-flex flex-column ga-6">
    <h1 class="page-title mb-6">{{ isEditing ? 'Editar produto' : 'Novo produto' }}</h1>

    <v-skeleton-loader v-if="loading" type="text, text, text, paragraph" aria-busy="true" />

    <StateMessage
      v-else-if="loadError"
      :icon="loadError.status === 404 ? 'mdi-package-variant-closed-remove' : 'mdi-alert-circle-outline'"
      :title="loadError.status === 404 ? 'Produto não encontrado' : 'Não foi possível carregar o produto'"
      :text="loadError.message"
    >
      <v-btn v-if="loadError.status !== 404" color="primary" variant="flat" prepend-icon="mdi-refresh" @click="loadProduct">Tentar novamente</v-btn>
      <v-btn v-else :to="{ name: 'products' }" color="primary" variant="flat">Ver todos os produtos</v-btn>
    </StateMessage>

    <ProductForm v-else :product="product" :submitting="submitting" :server-errors="serverErrors" @submit="save" @cancel="cancel" />
  </v-sheet>
</template>
