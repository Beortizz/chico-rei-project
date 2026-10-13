<script setup lang="ts">
defineProps<{
  title: string
  text: string
  confirmLabel?: string
  loading?: boolean
}>()

const open = defineModel<boolean>({ default: false })

defineEmits<{ confirm: [] }>()
</script>

<template>
  <v-dialog v-model="open" max-width="440" :persistent="loading">
    <v-card class="pa-2">
      <v-card-title class="font-weight-bold">{{ title }}</v-card-title>
      <v-card-text>{{ text }}</v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn :disabled="loading" @click="open = false">Cancelar</v-btn>
        <v-btn color="error" variant="flat" :loading="loading" @click="$emit('confirm')">
          {{ confirmLabel ?? 'Confirmar' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
