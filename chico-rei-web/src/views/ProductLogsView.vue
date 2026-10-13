<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { useDisplay } from 'vuetify'
import ListPagination from '@/components/ListPagination.vue'
import LogChanges from '@/components/LogChanges.vue'
import LogDetailsDialog from '@/components/LogDetailsDialog.vue'
import StateMessage from '@/components/StateMessage.vue'
import { useRouteQueryList } from '@/composables/useRouteQueryList'
import { listProductLogs } from '@/services/productLogs'
import type { ProductLog } from '@/types/api'
import { formatDateTime } from '@/utils/format'
import { LOG_ACTION_LABELS } from '@/utils/logChanges'
import { LOG_ACTION_OPTIONS, LOG_PER_PAGE_OPTIONS, parseLogQuery, toLogApiParams, toLogRouteQuery } from '@/utils/logQuery'

const { items: logs, meta, loading, error, state, apply, clearFilters, reload } = useRouteQueryList({
  routeName: 'product-logs',
  parse: parseLogQuery,
  toRouteQuery: toLogRouteQuery,
  toApiParams: toLogApiParams,
  fetch: listProductLogs,
})

const { xs, mdAndUp } = useDisplay()
const productMaxWidth = computed(() => (xs.value ? 64 : mdAndUp.value ? 240 : 100))
const selectedLog = ref<ProductLog | null>(null)
const detailsOpen = ref(false)

function showDetails(log: ProductLog) {
  selectedLog.value = log
  detailsOpen.value = true
}

const form = reactive({ name: state.value.name })

watch(() => state.value.name, (name) => {
  form.name = name
})
</script>

<template>
  <v-sheet tag="section" color="transparent">
    <h1 class="page-title mb-6">Histórico de alterações</h1>

    <v-card variant="flat" border class="pa-4 mb-6">
      <v-form @submit.prevent="apply({ name: form.name })">
        <v-row dense>
          <v-col cols="12" sm="6" md="5">
            <v-text-field v-model="form.name" label="Buscar por produto" prepend-inner-icon="mdi-magnify" clearable hide-details />
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-select
              :model-value="state.action"
              :items="LOG_ACTION_OPTIONS"
              label="Ação"
              hide-details
              @update:model-value="apply({ action: $event })"
            />
          </v-col>
          <v-col cols="12" md="4" class="d-flex ga-2 justify-end align-center">
            <v-btn variant="text" @click="clearFilters">Limpar</v-btn>
            <v-btn type="submit" color="primary" variant="flat">Buscar</v-btn>
          </v-col>
        </v-row>
      </v-form>
    </v-card>

    <v-skeleton-loader v-if="loading" type="table-thead, table-tbody" aria-busy="true" />

    <StateMessage v-else-if="error" icon="mdi-alert-circle-outline" title="Não foi possível carregar o histórico" :text="error">
      <v-btn color="primary" variant="flat" prepend-icon="mdi-refresh" @click="reload">Tentar novamente</v-btn>
    </StateMessage>

    <StateMessage
      v-else-if="logs.length === 0"
      icon="mdi-history"
      title="Nenhum registro encontrado"
      text="Quando produtos forem criados, alterados ou excluídos, o histórico aparecerá aqui."
    >
      <v-btn variant="outlined" @click="clearFilters">Limpar filtros</v-btn>
    </StateMessage>

    <v-card v-else variant="flat" border>
      <v-table>
        <thead>
          <tr>
            <th class="px-2 px-sm-3">Data</th>
            <th class="px-2 px-sm-3">Ação</th>
            <th class="px-2 px-sm-3">Produto</th>
            <th class="px-2 px-sm-3 d-none d-md-table-cell">Usuário</th>
            <th class="px-2 px-sm-3 d-none d-sm-table-cell">Alterações</th>
            <th class="px-2 px-sm-3 text-end">Detalhes</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in logs" :key="log.id">
            <td class="px-2 px-sm-3">{{ formatDateTime(log.created_at) }}</td>
            <td class="px-2 px-sm-3">
              <v-chip :color="LOG_ACTION_LABELS[log.action].color" size="small" label>{{ LOG_ACTION_LABELS[log.action].text }}</v-chip>
            </td>
            <td class="px-2 px-sm-3">
              <v-sheet color="transparent" :max-width="productMaxWidth" class="text-truncate" :title="log.product_name">
                <router-link v-if="log.action !== 'deleted'" :to="{ name: 'product-detail', params: { id: log.product_id } }">
                  {{ log.product_name }}
                </router-link>
                <span v-else>{{ log.product_name }}</span>
              </v-sheet>
            </td>
            <td class="px-2 px-sm-3 d-none d-md-table-cell">{{ log.user?.name ?? 'Sistema' }}</td>
            <td class="px-2 px-sm-3 d-none d-sm-table-cell"><LogChanges :log="log" /></td>
            <td class="px-2 px-sm-3 text-end">
              <v-btn
                icon="mdi-eye-outline"
                size="small"
                variant="text"
                :aria-label="`Ver detalhes do registro de ${log.product_name}`"
                title="Ver detalhes"
                @click="showDetails(log)"
              />
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>

    <LogDetailsDialog v-model="detailsOpen" :log="selectedLog" />

    <ListPagination
      v-if="meta && !loading && !error"
      :meta="meta"
      :per-page-options="LOG_PER_PAGE_OPTIONS"
      item-label="registros"
      @page="apply({ page: $event })"
      @per-page="apply({ per_page: $event })"
    />
  </v-sheet>
</template>
