<script setup lang="ts">
import { computed } from 'vue'
import LogFieldValue from '@/components/LogFieldValue.vue'
import type { ProductLog } from '@/types/api'
import { formatDateTime } from '@/utils/format'
import { getLogChanges, getLogSnapshot, LOG_ACTION_LABELS } from '@/utils/logChanges'

const props = defineProps<{ log: ProductLog | null }>()

const open = defineModel<boolean>({ default: false })

const changes = computed(() => (props.log ? getLogChanges(props.log) : []))
const snapshot = computed(() => (props.log ? getLogSnapshot(props.log) : []))
const action = computed(() => (props.log ? LOG_ACTION_LABELS[props.log.action] : null))
</script>

<template>
  <v-dialog v-model="open" max-width="720" scrollable>
    <v-card v-if="log && action" aria-labelledby="log-details-title">
      <v-card-title class="d-flex flex-wrap align-center ga-3 py-3">
        <span id="log-details-title" class="text-title-medium font-weight-bold">Detalhes do registro</span>
        <v-chip :color="action.color" class="flex-shrink-0" size="small" label>{{ action.text }}</v-chip>
        <v-spacer />
        <v-btn icon="mdi-close" variant="text" size="small" aria-label="Fechar" @click="open = false" />
      </v-card-title>

      <v-divider />

      <v-card-text class="pa-4 text-body-medium">
        <v-row dense class="mb-2">
          <v-col cols="12" sm="3" class="text-medium-emphasis">Data</v-col>
          <v-col cols="12" sm="9">{{ formatDateTime(log.created_at) }}</v-col>

          <v-col cols="12" sm="3" class="text-medium-emphasis">Produto</v-col>
          <v-col cols="12" sm="9" class="text-break">
            <router-link v-if="log.action !== 'deleted'" :to="{ name: 'product-detail', params: { id: log.product_id } }">
              {{ log.product_name }}
            </router-link>
            <span v-else>{{ log.product_name }}</span>
          </v-col>

          <v-col cols="12" sm="3" class="text-medium-emphasis">Usuário</v-col>
          <v-col cols="12" sm="9">{{ log.user?.name ?? 'Sistema' }}</v-col>
        </v-row>

        <v-divider class="my-4" />

        <h3 class="text-title-small font-weight-bold mb-3">Alterações</h3>

        <template v-if="log.action === 'updated'">
          <v-sheet v-for="change in changes" :key="change.field" border rounded class="pa-3 mb-3">
            <div class="text-body-medium font-weight-bold mb-2">{{ change.label }}</div>
            <v-row dense>
              <v-col cols="12" sm="6">
                <v-sheet rounded class="bg-red-lighten-5 text-grey-darken-4 pa-3 h-100">
                  <div class="d-flex align-center ga-1 text-body-small font-weight-bold text-red-darken-4 mb-1">
                    <v-icon icon="mdi-minus" size="x-small" />
                    Antes
                  </div>
                  <LogFieldValue :field="change.field" :value="change.before" :alt="`${change.label} anterior`" />
                </v-sheet>
              </v-col>
              <v-col cols="12" sm="6">
                <v-sheet rounded class="bg-green-lighten-5 text-grey-darken-4 pa-3 h-100">
                  <div class="d-flex align-center ga-1 text-body-small font-weight-bold text-green-darken-4 mb-1">
                    <v-icon icon="mdi-plus" size="x-small" />
                    Depois
                  </div>
                  <LogFieldValue :field="change.field" :value="change.after" :alt="`${change.label} novo`" />
                </v-sheet>
              </v-col>
            </v-row>
          </v-sheet>
        </template>

        <template v-else>
          <v-sheet v-for="item in snapshot" :key="item.field" border rounded class="pa-3 mb-3">
            <div class="text-body-medium font-weight-bold mb-1">{{ item.label }}</div>
            <LogFieldValue :field="item.field" :value="item.value" :alt="item.label" />
          </v-sheet>
        </template>
      </v-card-text>

      <v-divider />

      <v-card-actions>
        <v-spacer />
        <v-btn autofocus @click="open = false">Fechar</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
