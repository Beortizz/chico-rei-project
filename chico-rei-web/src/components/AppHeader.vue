<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useDisplay } from 'vuetify'
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'

const auth = useAuthStore()
const notifications = useNotificationsStore()
const router = useRouter()
const { smAndDown } = useDisplay()

const drawer = ref(false)

async function logout(): Promise<void> {
  await auth.logout()
  notifications.show('Você saiu da sua conta.')
  drawer.value = false
  router.push({ name: 'products' })
}
</script>

<template>
  <v-app-bar flat border="b" color="white">
    <v-app-bar-nav-icon v-if="smAndDown" aria-label="Abrir menu" @click="drawer = !drawer" />

    <v-app-bar-title>
      <router-link :to="{ name: 'products' }" class="text-decoration-none text-primary font-weight-black text-uppercase letter-spaced">
        Chico Rei
      </router-link>
    </v-app-bar-title>

    <template v-if="!smAndDown" #append>
      <v-btn :to="{ name: 'products' }" exact>Produtos</v-btn>

      <template v-if="auth.isAuthenticated">
        <v-btn :to="{ name: 'product-create' }" prepend-icon="mdi-plus">Novo produto</v-btn>
        <v-btn :to="{ name: 'product-logs' }" prepend-icon="mdi-history">Histórico</v-btn>

        <v-menu>
          <template #activator="{ props: activator }">
            <v-btn v-bind="activator" append-icon="mdi-chevron-down" class="ml-2">{{ auth.user?.name }}</v-btn>
          </template>
          <v-list density="compact">
            <v-list-item prepend-icon="mdi-logout" title="Sair" @click="logout" />
          </v-list>
        </v-menu>
      </template>

      <v-btn v-else :to="{ name: 'login' }" color="primary" variant="flat" class="ml-2">Entrar</v-btn>
    </template>
  </v-app-bar>

  <v-navigation-drawer v-if="smAndDown" v-model="drawer" temporary>
    <v-list nav>
      <v-list-item :to="{ name: 'products' }" prepend-icon="mdi-shopping-outline" title="Produtos" exact @click="drawer = false" />

      <template v-if="auth.isAuthenticated">
        <v-list-item :to="{ name: 'product-create' }" prepend-icon="mdi-plus" title="Novo produto" @click="drawer = false" />
        <v-list-item :to="{ name: 'product-logs' }" prepend-icon="mdi-history" title="Histórico" @click="drawer = false" />
        <v-divider class="my-2" />
        <v-list-item prepend-icon="mdi-logout" :title="`Sair (${auth.user?.name})`" @click="logout" />
      </template>

      <v-list-item v-else :to="{ name: 'login' }" prepend-icon="mdi-login" title="Entrar" @click="drawer = false" />
    </v-list>
  </v-navigation-drawer>
</template>
