<script setup lang="ts">
import { reactive, ref } from 'vue'
import type { Product, ProductFormValues } from '@/types/api'
import ImageUploadField from './ImageUploadField.vue'

const props = defineProps<{
  product: Product | null
  submitting: boolean
  serverErrors: Record<string, string[]>
}>()

const emit = defineEmits<{ submit: [values: ProductFormValues]; cancel: [] }>()

const formRef = ref<{ validate: () => Promise<{ valid: boolean }> } | null>(null)

const values = reactive<ProductFormValues>({
  name: props.product?.name ?? '',
  description: props.product?.description ?? '',
  price: props.product?.price.replace('.', ',') ?? '',
  stock: props.product ? String(props.product.stock) : '',
  category: props.product?.category ?? '',
  image: null,
  removeImage: false,
})

const required = (message: string) => (value: string) => value.trim() !== '' || message
const maxLength = (value: string) => value.length <= 255 || 'Use no máximo 255 caracteres.'
const validPrice = (value: string) => {
  const number = Number(value.replace(',', '.'))

  return (value.trim() !== '' && Number.isFinite(number) && number >= 0) || 'Informe um preço válido (ex.: 59,90).'
}
const validStock = (value: string) => /^\d+$/.test(value.trim()) || 'Informe um número inteiro maior ou igual a zero.'

async function submit(): Promise<void> {
  const result = await formRef.value?.validate()

  if (!result?.valid) return

  emit('submit', { ...values, price: values.price.replace(',', '.').trim(), stock: values.stock.trim() })
}
</script>

<template>
  <v-form ref="formRef" validate-on="blur lazy" @submit.prevent="submit">
    <v-row>
      <v-col cols="12">
        <v-text-field
          v-model="values.name"
          label="Nome *"
          aria-required="true"
          :rules="[required('Informe o nome do produto.'), maxLength]"
          :error-messages="serverErrors.name"
          counter="255"
        />
      </v-col>

      <v-col cols="12" sm="6">
        <v-text-field
          v-model="values.category"
          label="Categoria *"
          aria-required="true"
          :rules="[required('Informe a categoria.'), maxLength]"
          :error-messages="serverErrors.category"
        />
      </v-col>

      <v-col cols="6" sm="3">
        <v-text-field
          v-model="values.price"
          label="Preço *"
          aria-required="true"
          prefix="R$"
          inputmode="decimal"
          :rules="[validPrice]"
          :error-messages="serverErrors.price"
        />
      </v-col>

      <v-col cols="6" sm="3">
        <v-text-field
          v-model="values.stock"
          label="Estoque *"
          aria-required="true"
          inputmode="numeric"
          :rules="[validStock]"
          :error-messages="serverErrors.stock"
        />
      </v-col>

      <v-col cols="12">
        <v-textarea
          v-model="values.description"
          label="Descrição"
          rows="4"
          auto-grow
          :error-messages="serverErrors.description"
        />
      </v-col>

      <v-col cols="12">
        <ImageUploadField v-model:file="values.image" v-model:remove="values.removeImage" :current-url="product?.image ?? null" />
        <div v-for="message in serverErrors.image" :key="message" class="text-caption text-error mt-1" role="alert">{{ message }}</div>
      </v-col>
    </v-row>

    <p class="text-caption text-secondary mt-2">* Campos obrigatórios</p>

    <div class="d-flex justify-end ga-2 mt-4">
      <v-btn variant="text" :disabled="submitting" @click="emit('cancel')">Cancelar</v-btn>
      <v-btn type="submit" color="primary" variant="flat" :loading="submitting">
        {{ product ? 'Salvar alterações' : 'Criar produto' }}
      </v-btn>
    </div>
  </v-form>
</template>
