import { defineStore } from 'pinia'
import api from '@/lib/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    loading: false,
    error: null,
  }),
  getters: {
    isAuthenticated: (s) => !!s.user,
    isAdmin: (s) => s.user?.role === 'admin',
  },
  actions: {
    async fetchMe() {
      try {
        const { data } = await api.get('/api/auth/me')
        this.user = data.user
      } catch {
        this.user = null
      }
    },
    async login(payload) {
      this.loading = true; this.error = null
      try {
        const { data } = await api.post('/api/auth/login', payload)
        this.user = data.user
      } catch (e) {
        this.error = e.response?.data?.message || 'Login failed'
        throw e
      } finally { this.loading = false }
    },
    async register(payload) {
      this.loading = true; this.error = null
      try {
        const { data } = await api.post('/api/auth/register', payload)
        this.user = data.user
      } catch (e) {
        this.error = e.response?.data?.message || 'Registration failed'
        throw e
      } finally { this.loading = false }
    },
    async logout() {
      try { await api.post('/api/auth/logout') } finally { this.user = null }
    },
  },
})
