import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useNotificationsStore = defineStore('notifications', () => {
  const visible = ref(false)
  const message = ref('')
  const color = ref<'success' | 'error'>('success')

  function show(text: string, type: 'success' | 'error' = 'success'): void {
    message.value = text
    color.value = type
    visible.value = true
  }

  return { visible, message, color, show }
})
