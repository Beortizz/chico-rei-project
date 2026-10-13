import axios from 'axios'

const apiUrl: string = import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api'

export const http = axios.create({
  baseURL: apiUrl,
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: 'application/json' },
})

let unauthorizedHandler: (() => void) | null = null

/**
 * Registra o que fazer quando a API responde 401, evitando que este módulo dependa do router e das stores.
 */
export function onUnauthorized(handler: () => void): void {
  unauthorizedHandler = handler
}

http.interceptors.response.use(
  (response) => response,
  (error: unknown) => {
    if (axios.isAxiosError(error) && error.response?.status === 401) {
      unauthorizedHandler?.()
    }

    return Promise.reject(error)
  },
)

/**
 * O Sanctum exige o cookie XSRF antes de qualquer requisição que altere estado em modo SPA.
 */
export function ensureCsrfCookie(): Promise<unknown> {
  return axios.get(`${new URL(apiUrl, window.location.origin).origin}/sanctum/csrf-cookie`, { withCredentials: true })
}
