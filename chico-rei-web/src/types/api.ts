export interface User {
  id: number
  name: string
  email: string
}

export interface Product {
  id: number
  name: string
  description: string | null
  image: string | null
  price: string
  category: string
  stock: number
  in_stock: boolean
  created_at: string
  updated_at: string
}

export type ProductLogAction = 'created' | 'updated' | 'deleted'

export interface ProductLog {
  id: number
  product_id: number
  product_name: string
  action: ProductLogAction
  old_values: Record<string, unknown> | null
  new_values: Record<string, unknown> | null
  user: Pick<User, 'id' | 'name'> | null
  created_at: string
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  from: number | null
  to: number | null
  total: number
}

export interface Paginated<T> {
  data: T[]
  meta: PaginationMeta
}

export interface ApiErrorBody {
  message: string
  errors?: Record<string, string[]>
}

export interface ProductFormValues {
  name: string
  description: string
  price: string
  stock: string
  category: string
  image: File | null
  removeImage: boolean
}
