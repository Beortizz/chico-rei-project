<script setup lang="ts">
import StateMessage from '@/components/StateMessage.vue'
</script>

<template>
  <StateMessage icon="mdi-compass-off-outline" title="Página não encontrada" text="O endereço que você tentou acessar não existe.">
    <v-btn :to="{ name: 'products' }" color="primary" variant="flat">Ir para os produtos</v-btn>
  </StateMessage>
</template>
