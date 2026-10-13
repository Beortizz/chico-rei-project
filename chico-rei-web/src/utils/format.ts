const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' })
const dateTime = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' })

export function formatPrice(value: string | number): string {
  return currency.format(Number(value))
}

export function formatDateTime(iso: string): string {
  return dateTime.format(new Date(iso))
}

const API_URL: string = import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api'

/**
 * Os logs guardam o valor bruto da imagem: uma URL absoluta ou um caminho relativo ao storage público
 * da API, que é servido pela origem da API e não pelo prefixo /api.
 */
export function imageUrlFromStoragePath(path: string, apiUrl: string = API_URL): string {
  if (/^https?:\/\//i.test(path)) return path

  const origin = apiUrl.replace(/\/api\/?$/, '').replace(/\/$/, '')

  return `${origin}/storage/${path.replace(/^\/+/, '')}`
}
