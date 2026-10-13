import axios from 'axios'
import type { ApiErrorBody } from '@/types/api'

export interface NormalizedError {
  message: string
  status: number | null
  fieldErrors: Record<string, string[]>
}

const STATUS_MESSAGES: Record<number, string> = {
  401: 'Sua sessão expirou. Entre novamente.',
  403: 'Você não tem permissão para realizar esta ação.',
  404: 'Recurso não encontrado.',
  422: 'Verifique os dados informados.',
  429: 'Muitas tentativas. Aguarde um instante e tente novamente.',
}

export function isCanceled(error: unknown): boolean {
  return axios.isCancel(error)
}

/**
 * Converte qualquer falha de requisição no formato único usado pela interface, em português.
 */
export function normalizeError(error: unknown): NormalizedError {
  if (!axios.isAxiosError<ApiErrorBody>(error)) {
    return { message: 'Ocorreu um erro inesperado.', status: null, fieldErrors: {} }
  }

  if (!error.response) {
    return { message: 'Não foi possível conectar ao servidor. Verifique sua conexão.', status: null, fieldErrors: {} }
  }

  const { status, data } = error.response

  return {
    message: STATUS_MESSAGES[status] ?? (status >= 500 ? 'Erro no servidor. Tente novamente em instantes.' : 'Não foi possível concluir a ação.'),
    status,
    fieldErrors: data?.errors ?? {},
  }
}
