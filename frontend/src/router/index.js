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
  { path: '/profile', name: 'profile', component: () => import('@/pages/Profile.vue'), meta: { auth: true } },
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

  const needsSession = !!(to.meta.auth || to.meta.admin || to.meta.guest)

  if (needsSession) {
    await auth.initSession()
  } else if (!auth.initialized && !auth.initPromise) {
    auth.initSession()
  }

  if (to.meta.auth && !auth.isAuthenticated) return { name: 'login', query: { next: to.fullPath } }
  if (to.meta.admin && !auth.isAdmin) return { name: 'home' }
  if (to.meta.guest && auth.isAuthenticated) return { name: 'dashboard' }
})

export default router
