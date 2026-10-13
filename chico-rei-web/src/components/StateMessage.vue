<script setup lang="ts">
defineProps<{
  icon: string
  title: string
  text?: string
}>()
</script>

<template>
  <div class="d-flex flex-column align-center text-center py-12 px-4" role="status">
    <v-icon :icon="icon" size="56" color="secondary" class="mb-4" />
    <h2 class="text-h6 font-weight-bold mb-1">{{ title }}</h2>
    <p v-if="text" class="text-body-2 text-secondary mb-4" style="max-width: 420px">{{ text }}</p>
    <slot />
  </div>
</template>
