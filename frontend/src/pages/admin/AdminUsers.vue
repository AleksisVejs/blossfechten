<script setup>
import { onMounted, ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'
import { useToast } from '@/composables/useToast'

const { t } = useI18n()
const toast = useToast()

const users = ref([])
const loading = ref(false)
const search = ref('')
const editingRanks = ref({}) // id -> draft string

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/api/admin/users')
    users.value = data.data.data
  } catch {
    toast.error(t('admin.error_loading'))
  } finally {
    loading.value = false
  }
}
onMounted(load)

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return users.value
  return users.value.filter(u =>
    (u.name || '').toLowerCase().includes(q) ||
    (u.email || '').toLowerCase().includes(q) ||
    (u.rank || '').toLowerCase().includes(q)
  )
})

async function update(u, patch) {
  try {
    await api.put(`/api/admin/users/${u.id}`, {
      role: patch.role ?? u.role,
      rank: patch.rank ?? u.rank ?? '',
    })
    Object.assign(u, patch)
    toast.success(t('admin.saved'))
  } catch {
    toast.error(t('admin.error_saving'))
  }
}

function commitRank(u) {
  const draft = editingRanks.value[u.id]
  if (draft === undefined) return
  if (draft !== (u.rank ?? '')) update(u, { rank: draft })
  delete editingRanks.value[u.id]
}
</script>

<template>
  <div>
    <div class="flex flex-wrap gap-3 mb-4 items-center">
      <input
        v-model="search"
        type="search"
        :placeholder="t('admin.search')"
        class="field-input !w-auto !py-1.5 text-sm md:min-w-[18rem]"
      />
      <span class="text-xs text-ink-500 font-sans ml-auto">{{ filtered.length }} / {{ users.length }}</span>
    </div>

    <div v-if="loading" class="card p-6 text-center text-ink-500 italic font-sans">{{ t('admin.loading') }}</div>
    <div v-else-if="!filtered.length" class="card p-6 text-center text-ink-500 italic font-sans">{{ t('admin.no_results') }}</div>
    <div v-else class="card overflow-x-auto">
      <table class="w-full text-sm font-sans">
        <thead class="bg-parchment-200/60 text-left">
          <tr>
            <th class="p-3">{{ t('admin.name') }}</th>
            <th class="p-3">{{ t('admin.email') }}</th>
            <th class="p-3">{{ t('admin.rank') }}</th>
            <th class="p-3">{{ t('admin.role') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in filtered" :key="u.id" class="border-t border-parchment-300/50">
            <td class="p-3">
              <div>{{ u.name }}</div>
              <div v-if="u.phone" class="text-xs text-ink-500">{{ u.phone }}</div>
            </td>
            <td class="p-3 text-ink-500 break-all">{{ u.email }}</td>
            <td class="p-3">
              <input
                :value="editingRanks[u.id] !== undefined ? editingRanks[u.id] : (u.rank || '')"
                @input="editingRanks[u.id] = $event.target.value"
                @blur="commitRank(u)"
                @keydown.enter.prevent="commitRank(u)"
                class="field-input !py-1.5 !text-sm w-32"
                :placeholder="t('admin.rank')"
              />
            </td>
            <td class="p-3">
              <select :value="u.role" @change="update(u, { role: $event.target.value })" class="field-input !py-1.5 !text-sm w-40">
                <option value="member">member</option>
                <option value="instructor">instructor</option>
                <option value="admin">admin</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
