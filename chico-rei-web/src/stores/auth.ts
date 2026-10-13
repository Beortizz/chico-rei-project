import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import * as authApi from '@/services/auth'
import type { User } from '@/types/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const initialized = ref(false)
  let pendingInit: Promise<void> | null = null

  const isAuthenticated = computed(() => user.value !== null)

  /**
   * Descobre uma única vez se já existe sessão ativa; chamadas simultâneas compartilham a mesma requisição.
   */
  function init(): Promise<void> {
    if (initialized.value) return Promise.resolve()

    pendingInit ??= authApi
      .fetchUser()
      .then((fetched) => {
        user.value = fetched
      })
      .catch(() => {
        user.value = null
      })
      .finally(() => {
        initialized.value = true
        pendingInit = null
      })

    return pendingInit
  }

  async function login(email: string, password: string): Promise<void> {
    await authApi.login(email, password)
    user.value = await authApi.fetchUser()
  }

  async function logout(): Promise<void> {
    try {
      await authApi.logout()
    } finally {
      user.value = null
    }
  }

  function clear(): void {
    user.value = null
  }

  return { user, initialized, isAuthenticated, init, login, logout, clear }
})
