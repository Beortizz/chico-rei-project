<script setup lang="ts">
import { reactive, watch } from 'vue'
import ListPagination from '@/components/ListPagination.vue'
import LogChanges from '@/components/LogChanges.vue'
import StateMessage from '@/components/StateMessage.vue'
import { useRouteQueryList } from '@/composables/useRouteQueryList'
import { listProductLogs } from '@/services/productLogs'
import type { ProductLogAction } from '@/types/api'
import { formatDateTime } from '@/utils/format'
import { LOG_ACTION_OPTIONS, LOG_PER_PAGE_OPTIONS, parseLogQuery, toLogApiParams, toLogRouteQuery } from '@/utils/logQuery'

const ACTION_LABELS: Record<ProductLogAction, { text: string; color: string }> = {
  created: { text: 'Criação', color: 'success' },
  updated: { text: 'Atualização', color: 'info' },
  deleted: { text: 'Exclusão', color: 'error' },
}

const { items: logs, meta, loading, error, state, apply, clearFilters, reload } = useRouteQueryList({
  routeName: 'product-logs',
  parse: parseLogQuery,
  toRouteQuery: toLogRouteQuery,
  toApiParams: toLogApiParams,
  fetch: listProductLogs,
})

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
            <th>Data</th>
            <th>Ação</th>
            <th>Produto</th>
            <th>Usuário</th>
            <th>Alterações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in logs" :key="log.id">
            <td class="text-no-wrap">{{ formatDateTime(log.created_at) }}</td>
            <td>
              <v-chip :color="ACTION_LABELS[log.action].color" size="small" label>{{ ACTION_LABELS[log.action].text }}</v-chip>
            </td>
            <td>
              <router-link v-if="log.action !== 'deleted'" :to="{ name: 'product-detail', params: { id: log.product_id } }">
                {{ log.product_name }}
              </router-link>
              <span v-else>{{ log.product_name }}</span>
            </td>
            <td>{{ log.user?.name ?? 'Sistema' }}</td>
            <td class="py-2"><LogChanges :log="log" /></td>
          </tr>
        </tbody>
      </v-table>
    </v-card>

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
