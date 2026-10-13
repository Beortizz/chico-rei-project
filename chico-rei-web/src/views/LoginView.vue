<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { normalizeError } from '@/utils/errors'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const submitting = ref(false)
const errorMessage = ref('')

const requiredEmail = (value: string) => value.trim() !== '' || 'Informe seu e-mail.'
const validEmail = (value: string) => /^\S+@\S+\.\S+$/.test(value) || 'Informe um e-mail válido.'
const requiredPassword = (value: string) => value !== '' || 'Informe sua senha.'

/**
 * Só aceita redirecionamento para caminhos internos, evitando que a query string leve a outro site.
 */
function redirectTarget(): string {
  const target = route.query.redirect

  return typeof target === 'string' && target.startsWith('/') && !target.startsWith('//') ? target : '/produtos'
}

async function submit(): Promise<void> {
  submitting.value = true
  errorMessage.value = ''

  try {
    await auth.login(email.value.trim(), password.value)
    router.replace(redirectTarget())
  } catch (e) {
    const error = normalizeError(e)
    errorMessage.value = error.status === 422 ? 'E-mail ou senha incorretos.' : error.message
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <v-sheet tag="section" color="transparent" class="d-flex justify-center">
    <v-card variant="flat" border class="pa-6 w-100" max-width="420">
      <h1 class="page-title mb-1">Entrar</h1>
      <p class="text-body-medium text-secondary mb-6">Acesse para cadastrar e gerenciar produtos.</p>

      <v-alert v-if="errorMessage" type="error" variant="tonal" density="comfortable" class="mb-4" role="alert">
        {{ errorMessage }}
      </v-alert>

      <v-form validate-on="submit lazy" @submit.prevent="submit">
        <v-text-field
          v-model="email"
          label="E-mail"
          type="email"
          autocomplete="username"
          prepend-inner-icon="mdi-email-outline"
          :rules="[requiredEmail, validEmail]"
        />
        <v-text-field
          v-model="password"
          label="Senha"
          autocomplete="current-password"
          prepend-inner-icon="mdi-lock-outline"
          :type="showPassword ? 'text' : 'password'"
          :append-inner-icon="showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
          :rules="[requiredPassword]"
          @click:append-inner="showPassword = !showPassword"
        />
        <v-btn type="submit" color="primary" variant="flat" block size="large" :loading="submitting" class="mt-2">Entrar</v-btn>
      </v-form>
    </v-card>
  </v-sheet>
</template>
