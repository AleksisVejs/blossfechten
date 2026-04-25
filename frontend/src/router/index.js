import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  { path: '/', name: 'home', component: () => import('@/pages/Home.vue') },
  { path: '/about', name: 'about', component: () => import('@/pages/About.vue') },
  { path: '/meyer', name: 'meyer', redirect: { name: 'about' } },
  { path: '/schedule', name: 'schedule', component: () => import('@/pages/Schedule.vue') },
  { path: '/members', name: 'members', component: () => import('@/pages/Members.vue') },
  { path: '/contact', name: 'contact', component: () => import('@/pages/Contact.vue') },
  { path: '/login', name: 'login', component: () => import('@/pages/Login.vue'), meta: { guest: true } },
  { path: '/register', name: 'register', component: () => import('@/pages/Register.vue'), meta: { guest: true } },
  { path: '/dashboard', name: 'dashboard', component: () => import('@/pages/Dashboard.vue'), meta: { auth: true } },
  { path: '/admin', name: 'admin', component: () => import('@/pages/Admin.vue'), meta: { auth: true, admin: true } },
  { path: '/:pathMatch(.*)*', component: () => import('@/pages/NotFound.vue') },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  if (auth.user === null && !auth._tried) {
    auth._tried = true
    await auth.fetchMe()
  }
  if (to.meta.auth && !auth.isAuthenticated) return { name: 'login', query: { next: to.fullPath } }
  if (to.meta.admin && !auth.isAdmin) return { name: 'home' }
  if (to.meta.guest && auth.isAuthenticated) return { name: 'dashboard' }
})

export default router
