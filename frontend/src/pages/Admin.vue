<script setup>
import { onMounted, ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'

const { t, locale } = useI18n()
const tab = ref('trainings')
const trainings = ref([])
const users = ref([])
const editing = ref(null)
const form = ref(emptyForm())

function emptyForm() {
  const d = new Date()
  return {
    id: null,
    starts_at: d.toISOString().slice(0, 16),
    ends_at: new Date(d.getTime() + 2 * 3600 * 1000).toISOString().slice(0, 16),
    location: 'Riga',
    focus: 'Longsword',
    title: { lv: '', en: '', ru: '', cs: '', de: '' },
    description: { lv: '', en: '', ru: '', cs: '', de: '' },
    capacity: 20,
    members_only: false,
    cancelled: false,
  }
}

async function load() {
  if (tab.value === 'trainings') {
    const { data } = await api.get('/api/admin/trainings')
    trainings.value = data.data.data
  } else {
    const { data } = await api.get('/api/admin/users')
    users.value = data.data.data
  }
}
onMounted(load)

function edit(row) {
  editing.value = row.id
  form.value = {
    ...row,
    starts_at: row.starts_at.slice(0, 16),
    ends_at: row.ends_at.slice(0, 16),
    title: row.title || { lv: '', en: '', ru: '', cs: '', de: '' },
    description: row.description || { lv: '', en: '', ru: '', cs: '', de: '' },
  }
}
function newOne() { editing.value = 'new'; form.value = emptyForm() }
function cancel() { editing.value = null }

async function save() {
  if (editing.value === 'new') await api.post('/api/admin/trainings', form.value)
  else await api.put(`/api/admin/trainings/${editing.value}`, form.value)
  editing.value = null
  await load()
}
async function remove(row) {
  if (!confirm(t('admin.confirm_delete'))) return
  await api.delete(`/api/admin/trainings/${row.id}`)
  await load()
}
async function updateRole(u, role) {
  await api.put(`/api/admin/users/${u.id}`, { role, rank: u.rank })
  await load()
}

const fmt = computed(() => new Intl.DateTimeFormat(locale.value, { dateStyle: 'medium', timeStyle: 'short' }))
</script>

<template>
  <section class="max-w-6xl mx-auto px-4 py-12">
    <h1>{{ t('admin.title') }}</h1>
    <div class="divider-engraved my-6 w-1/3"></div>

    <div class="flex gap-2 mb-6 font-sans text-sm uppercase tracking-wider">
      <button @click="tab='trainings'; load()" :class="['btn-ghost', tab==='trainings' && 'bg-parchment-200']">{{ t('admin.trainings') }}</button>
      <button @click="tab='users'; load()" :class="['btn-ghost', tab==='users' && 'bg-parchment-200']">{{ t('admin.users') }}</button>
    </div>

    <!-- Trainings tab -->
    <div v-if="tab==='trainings'">
      <button class="btn-primary mb-4" @click="newOne">{{ t('admin.new_training') }}</button>
      <div class="card overflow-x-auto">
        <table class="w-full text-sm font-sans">
          <thead class="bg-parchment-200/60 text-left">
            <tr>
              <th class="p-3">{{ t('admin.date') }}</th>
              <th class="p-3">{{ t('admin.focus') }}</th>
              <th class="p-3">{{ t('admin.capacity') }}</th>
              <th class="p-3">{{ t('admin.registrations') }}</th>
              <th class="p-3"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in trainings" :key="s.id" class="border-t border-parchment-300/50">
              <td class="p-3">{{ fmt.format(new Date(s.starts_at)) }}</td>
              <td class="p-3">{{ s.focus }}</td>
              <td class="p-3">{{ s.capacity }}</td>
              <td class="p-3">{{ s.registrations_count ?? 0 }}</td>
              <td class="p-3 text-right space-x-2">
                <button class="btn-ghost text-xs" @click="edit(s)">{{ t('admin.edit') }}</button>
                <button class="btn-ghost text-xs text-oxblood-500" @click="remove(s)">{{ t('admin.delete') }}</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="editing" class="fixed inset-0 bg-ink-900/60 flex items-center justify-center z-50 p-4">
        <div class="card p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <h3 class="mb-4">{{ editing === 'new' ? t('admin.new_training') : t('admin.edit') }}</h3>
          <div class="grid md:grid-cols-2 gap-4 font-sans text-sm">
            <label>{{ t('admin.date') }} / Start
              <input v-model="form.starts_at" type="datetime-local" class="block w-full mt-1 px-3 py-2 border border-parchment-300 bg-parchment-50 rounded-sm" />
            </label>
            <label>End
              <input v-model="form.ends_at" type="datetime-local" class="block w-full mt-1 px-3 py-2 border border-parchment-300 bg-parchment-50 rounded-sm" />
            </label>
            <label>{{ t('admin.focus') }}
              <input v-model="form.focus" class="block w-full mt-1 px-3 py-2 border border-parchment-300 bg-parchment-50 rounded-sm" />
            </label>
            <label>Location
              <input v-model="form.location" class="block w-full mt-1 px-3 py-2 border border-parchment-300 bg-parchment-50 rounded-sm" />
            </label>
            <label>{{ t('admin.capacity') }}
              <input v-model.number="form.capacity" type="number" min="1" class="block w-full mt-1 px-3 py-2 border border-parchment-300 bg-parchment-50 rounded-sm" />
            </label>
            <label class="flex items-center gap-2 mt-6">
              <input v-model="form.members_only" type="checkbox" /> Members only
            </label>
            <label class="flex items-center gap-2">
              <input v-model="form.cancelled" type="checkbox" /> Cancelled
            </label>
          </div>

          <h4 class="mt-4 mb-2 uppercase text-xs tracking-widest text-ink-500">Title (per language)</h4>
          <div class="grid md:grid-cols-2 gap-2">
            <label v-for="l in ['lv','en','ru','cs','de']" :key="'t-'+l" class="text-xs">
              {{ l.toUpperCase() }}
              <input v-model="form.title[l]" class="block w-full px-3 py-2 border border-parchment-300 bg-parchment-50 rounded-sm" />
            </label>
          </div>
          <h4 class="mt-4 mb-2 uppercase text-xs tracking-widest text-ink-500">Description</h4>
          <div class="grid md:grid-cols-2 gap-2">
            <label v-for="l in ['lv','en','ru','cs','de']" :key="'d-'+l" class="text-xs">
              {{ l.toUpperCase() }}
              <textarea v-model="form.description[l]" rows="2" class="block w-full px-3 py-2 border border-parchment-300 bg-parchment-50 rounded-sm"></textarea>
            </label>
          </div>

          <div class="mt-6 flex gap-2 justify-end">
            <button class="btn-ghost" @click="cancel">{{ t('admin.cancel') }}</button>
            <button class="btn-primary" @click="save">{{ t('admin.save') }}</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Users tab -->
    <div v-else class="card overflow-x-auto">
      <table class="w-full text-sm font-sans">
        <thead class="bg-parchment-200/60 text-left">
          <tr><th class="p-3">Name</th><th class="p-3">Email</th><th class="p-3">Rank</th><th class="p-3">{{ t('admin.role') }}</th></tr>
        </thead>
        <tbody>
          <tr v-for="u in users" :key="u.id" class="border-t border-parchment-300/50">
            <td class="p-3">{{ u.name }}</td>
            <td class="p-3">{{ u.email }}</td>
            <td class="p-3">{{ u.rank }}</td>
            <td class="p-3">
              <select :value="u.role" @change="updateRole(u, $event.target.value)"
                class="px-2 py-1 border border-parchment-300 bg-parchment-50 rounded-sm">
                <option value="member">member</option>
                <option value="instructor">instructor</option>
                <option value="admin">admin</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>
