import { defineStore } from 'pinia'
import api from '@/lib/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    loading: false,
    error: null,
    initialized: false,
    initPromise: null,
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
      } finally {
        this.initialized = true
      }
    },
    async initSession() {
      if (this.initialized) return this.user
      if (!this.initPromise) {
        this.initPromise = this.fetchMe().finally(() => {
          this.initPromise = null
        })
      }
      await this.initPromise
      return this.user
    },
    async login(payload) {
      this.loading = true; this.error = null
      try {
        const { data } = await api.post('/api/auth/login', payload)
        this.user = data.user
        this.initialized = true
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
        this.initialized = true
      } catch (e) {
        this.error = e.response?.data?.message || 'Registration failed'
        throw e
      } finally { this.loading = false }
    },
    async logout() {
      try { await api.post('/api/auth/logout') } finally {
        this.user = null
        this.initialized = true
      }
    },
    async changePassword(payload) {
      const { data } = await api.post('/api/auth/password/change', payload)
      return data
    },
    async resendVerification() {
      const { data } = await api.post('/api/auth/email/resend')
      return data
    },
    async forgotPassword(email) {
      const { data } = await api.post('/api/auth/password/forgot', { email })
      return data
    },
    async resetPassword(payload) {
      const { data } = await api.post('/api/auth/password/reset', payload)
      return data
    },
    async updateProfile(payload) {
      this.loading = true; this.error = null
      try {
        const { data } = await api.put('/api/auth/profile', payload)
        this.user = data.user
        this.initialized = true
        return data.user
      } catch (e) {
        this.error = e.response?.data?.message || 'Profile update failed'
        throw e
      } finally { this.loading = false }
    },
    async deleteProfile() {
      this.loading = true; this.error = null
      try {
        const res = await api.delete('/api/auth/profile')
        // Some deployments may still return 204 (no JSON). In that case the request
        // succeeded and we treat it as success, since the backend logs out the session.
        const data = res?.data
        if (data && typeof data === 'object' && Object.prototype.hasOwnProperty.call(data, 'deleted') && data.deleted !== true) {
          throw new Error(data?.still_exists ? 'Account still exists.' : 'Account deletion failed.')
        }
        this.user = null
        this.initialized = true
        return true
      } catch (e) {
        this.error = e.response?.data?.message || 'Profile deletion failed'
        throw e
      } finally { this.loading = false }
    },
  },
})
