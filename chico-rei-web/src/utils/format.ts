const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' })
const dateTime = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' })

export function formatPrice(value: string | number): string {
  return currency.format(Number(value))
}

export function formatDateTime(iso: string): string {
  return dateTime.format(new Date(iso))
}
