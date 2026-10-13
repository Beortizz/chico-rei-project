import { http } from './http'
import type { Paginated, Product, ProductFormValues } from '@/types/api'

/**
 * Multipart é necessário para enviar o arquivo; o PHP não lê multipart em PUT,
 * então a atualização usa POST com method spoofing.
 */
function toFormData(values: ProductFormValues, method?: 'PUT'): FormData {
  const form = new FormData()

  form.append('name', values.name)
  form.append('description', values.description)
  form.append('price', values.price)
  form.append('stock', values.stock)
  form.append('category', values.category)

  if (values.image) form.append('image', values.image)
  if (values.removeImage) form.append('remove_image', '1')
  if (method) form.append('_method', method)

  return form
}

export async function listProducts(params: Record<string, string | number>, signal?: AbortSignal): Promise<Paginated<Product>> {
  const { data } = await http.get<Paginated<Product>>('/products', { params, signal })

  return data
}

export async function getProduct(id: number | string, signal?: AbortSignal): Promise<Product> {
  const { data } = await http.get<{ data: Product }>(`/products/${id}`, { signal })

  return data.data
}

export async function createProduct(values: ProductFormValues): Promise<Product> {
  const { data } = await http.post<{ data: Product }>('/products', toFormData(values))

  return data.data
}

export async function updateProduct(id: number | string, values: ProductFormValues): Promise<Product> {
  const { data } = await http.post<{ data: Product }>(`/products/${id}`, toFormData(values, 'PUT'))

  return data.data
}

export async function deleteProduct(id: number | string): Promise<void> {
  await http.delete(`/products/${id}`)
}
