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
    <h2 class="text-title-large font-weight-bold mb-1">{{ title }}</h2>
    <v-sheet v-if="text" tag="p" color="transparent" max-width="420" class="text-body-medium text-secondary mb-4">{{ text }}</v-sheet>
    <slot />
  </div>
</template>
