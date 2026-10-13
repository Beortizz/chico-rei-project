<script setup lang="ts">
import AppHeader from '@/components/AppHeader.vue'
import { useNotificationsStore } from '@/stores/notifications'

const notifications = useNotificationsStore()
</script>

<template>
  <v-app>
    <AppHeader />

    <v-main>
      <v-container class="py-8" max-width="1280">
        <router-view />
      </v-container>
    </v-main>

    <v-snackbar v-model="notifications.visible" :color="notifications.color" location="bottom" timeout="4000">
      {{ notifications.message }}
      <template #actions>
        <v-btn variant="text" @click="notifications.visible = false">Fechar</v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>
