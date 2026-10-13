<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { useDisplay } from 'vuetify'
import { STOCK_OPTIONS, type ProductFiltersState } from '@/utils/productQuery'

const props = defineProps<{ modelValue: ProductFiltersState }>()

const emit = defineEmits<{ apply: [changes: Partial<ProductFiltersState>]; clear: [] }>()

const { mdAndUp } = useDisplay()
const expanded = ref(false)

const form = reactive({
  name: props.modelValue.name,
  category: props.modelValue.category,
  min_price: props.modelValue.min_price,
  max_price: props.modelValue.max_price,
  in_stock: props.modelValue.in_stock,
})

watch(
  () => props.modelValue,
  (value) => Object.assign(form, {
    name: value.name,
    category: value.category,
    min_price: value.min_price,
    max_price: value.max_price,
    in_stock: value.in_stock,
  }),
)

const priceRangeInvalid = computed(
  () => form.min_price !== '' && form.max_price !== '' && Number(form.max_price) < Number(form.min_price),
)

const activeCount = computed(
  () => [props.modelValue.name, props.modelValue.category, props.modelValue.min_price, props.modelValue.max_price, props.modelValue.in_stock]
    .filter(Boolean).length,
)

function submit(): void {
  if (priceRangeInvalid.value) return

  emit('apply', { ...form })
}

function nonNegative(value: string): true | string {
  return value === '' || Number(value) >= 0 || 'Informe um valor maior ou igual a zero.'
}
</script>

<template>
  <v-card variant="flat" border class="pa-4">
    <div v-if="!mdAndUp" class="d-flex align-center justify-space-between">
      <span class="font-weight-bold">Filtros<span v-if="activeCount" class="text-accent"> ({{ activeCount }})</span></span>
      <v-btn
        variant="text"
        :append-icon="expanded ? 'mdi-chevron-up' : 'mdi-chevron-down'"
        :aria-expanded="expanded"
        @click="expanded = !expanded"
      >
        {{ expanded ? 'Ocultar' : 'Mostrar' }}
      </v-btn>
    </div>

    <v-expand-transition>
      <v-form v-show="mdAndUp || expanded" :class="{ 'mt-4': !mdAndUp }" @submit.prevent="submit">
        <v-row dense>
          <v-col cols="12" md="4">
            <v-text-field v-model="form.name" label="Buscar por nome" prepend-inner-icon="mdi-magnify" clearable hide-details="auto" />
          </v-col>
          <v-col cols="12" sm="6" md="2">
            <v-text-field v-model="form.category" label="Categoria" clearable hide-details="auto" />
          </v-col>
          <v-col cols="6" sm="3" md="2">
            <v-text-field
              v-model="form.min_price"
              label="Preço mín."
              type="number"
              min="0"
              prefix="R$"
              :rules="[nonNegative]"
              hide-details="auto"
            />
          </v-col>
          <v-col cols="6" sm="3" md="2">
            <v-text-field
              v-model="form.max_price"
              label="Preço máx."
              type="number"
              min="0"
              prefix="R$"
              :rules="[nonNegative, () => !priceRangeInvalid || 'Menor que o mínimo.']"
              hide-details="auto"
            />
          </v-col>
          <v-col cols="12" md="2">
            <v-select v-model="form.in_stock" :items="STOCK_OPTIONS" label="Disponibilidade" hide-details="auto" />
          </v-col>
        </v-row>

        <div class="d-flex justify-end ga-2 mt-3">
          <v-btn variant="text" @click="$emit('clear')">Limpar</v-btn>
          <v-btn type="submit" color="primary" variant="flat" :disabled="priceRangeInvalid">Aplicar filtros</v-btn>
        </div>
      </v-form>
    </v-expand-transition>
  </v-card>
</template>
