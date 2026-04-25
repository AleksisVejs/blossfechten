import { defineStore } from 'pinia'
import api from '@/lib/api'

export const useTrainingsStore = defineStore('trainings', {
  state: () => ({
    list: [],
    mine: [],
    loading: false,
  }),
  actions: {
    async fetch() {
      this.loading = true
      try {
        const { data } = await api.get('/api/trainings')
        this.list = data.data
      } finally { this.loading = false }
    },
    async fetchMine() {
      const { data } = await api.get('/api/me/registrations')
      this.mine = data.data
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
