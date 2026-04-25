import { defineStore } from 'pinia'
import api from '@/lib/api'
import { loadCachedApi, saveCachedApi } from '@/lib/pageCache'

export const useTrainingsStore = defineStore('trainings', {
  state: () => ({
    list: loadCachedApi('trainings') || [],
    mine: loadCachedApi('my-registrations') || [],
    loading: false,
  }),
  actions: {
    async fetch() {
      this.loading = true
      try {
        const { data } = await api.get('/api/trainings')
        this.list = data.data
        saveCachedApi('trainings', data.data)
      } finally { this.loading = false }
    },
    async fetchMine() {
      const { data } = await api.get('/api/me/registrations')
      this.mine = data.data
      saveCachedApi('my-registrations', data.data)
    },
    async register(id, note = null) {
      await api.post(`/api/trainings/${id}/register`, { note })
      await this.fetch()
    },
    async unregister(id) {
      await api.delete(`/api/trainings/${id}/register`)
      await this.fetch()
    },
  },
})
