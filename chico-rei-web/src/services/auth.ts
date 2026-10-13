import { ensureCsrfCookie, http } from './http'
import type { User } from '@/types/api'

export async function login(email: string, password: string): Promise<void> {
  await ensureCsrfCookie()
  await http.post('/login', { email, password })
}

export async function logout(): Promise<void> {
  await http.post('/logout')
}

export async function fetchUser(): Promise<User> {
  const { data } = await http.get<User>('/user')

  return data
}
