import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

declare module 'vue-router' {
  interface RouteMeta {
    requiresAuth?: boolean
    guestOnly?: boolean
    title?: string
  }
}

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    { path: '/', redirect: { name: 'products' } },
    { path: '/produtos', name: 'products', component: () => import('@/views/ProductListView.vue'), meta: { title: 'Produtos' } },
    { path: '/entrar', name: 'login', component: () => import('@/views/LoginView.vue'), meta: { guestOnly: true, title: 'Entrar' } },
    {
      path: '/produtos/novo',
      name: 'product-create',
      component: () => import('@/views/ProductFormView.vue'),
      meta: { requiresAuth: true, title: 'Novo produto' },
    },
    {
      path: '/produtos/:id(\\d+)',
      name: 'product-detail',
      component: () => import('@/views/ProductDetailView.vue'),
      props: true,
      meta: { title: 'Produto' },
    },
    {
      path: '/produtos/:id(\\d+)/editar',
      name: 'product-edit',
      component: () => import('@/views/ProductFormView.vue'),
      props: true,
      meta: { requiresAuth: true, title: 'Editar produto' },
    },
    {
      path: '/historico',
      name: 'product-logs',
      component: () => import('@/views/ProductLogsView.vue'),
      meta: { requiresAuth: true, title: 'Histórico' },
    },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('@/views/NotFoundView.vue'), meta: { title: 'Página não encontrada' } },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  await auth.init()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { name: 'products' }
  }

  return true
})

router.afterEach((to) => {
  document.title = to.meta.title ? `${to.meta.title} | Chico Rei Store` : 'Chico Rei Store'
})

export default router
