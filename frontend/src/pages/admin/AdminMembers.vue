<script setup>
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/ConfirmDialog.vue'

const { t } = useI18n()
const toast = useToast()

const members = ref([])
const loading = ref(false)
const editing = ref(null) // 'new' | id | null
const form = ref(emptyForm())
const saving = ref(false)
const uploadingPhoto = ref(false)
const confirmingDelete = ref(null)
const deletingNow = ref(false)

function emptyForm() {
  return {
    id: null,
    full_name: '',
    photo_url: '',
    is_instructor: false,
    sort_order: 0,
  }
}

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/api/admin/members')
    members.value = data.data
  } catch {
    toast.error(t('admin.error_loading'))
  } finally {
    loading.value = false
  }
}
onMounted(load)

function edit(m) {
  editing.value = m.id
  form.value = {
    ...m,
  }
}
function newOne() { editing.value = 'new'; form.value = emptyForm() }
function cancel() { editing.value = null }

async function save() {
  saving.value = true
  try {
    const payload = { ...form.value }
    delete payload.bio
    delete payload.role_title
    delete payload.rank

    if (editing.value === 'new') await api.post('/api/admin/members', payload)
    else await api.put(`/api/admin/members/${editing.value}`, payload)
    editing.value = null
    toast.success(t('admin.saved'))
    await load()
  } catch (e) {
    toast.error(e.response?.data?.message || t('admin.error_saving'))
  } finally {
    saving.value = false
  }
}

async function onPhotoFileChange(event) {
  const file = event.target.files?.[0]
  if (!file) return

  uploadingPhoto.value = true
  try {
    const payload = new FormData()
    payload.append('photo', file)
    const { data } = await api.post('/api/admin/members/upload-photo', payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    form.value.photo_url = data?.data?.photo_url || form.value.photo_url
    toast.success(t('admin.saved'))
  } catch (e) {
    toast.error(e.response?.data?.message || t('admin.error_saving'))
  } finally {
    uploadingPhoto.value = false
    event.target.value = ''
  }
}

async function confirmDelete() {
  if (!confirmingDelete.value) return
  deletingNow.value = true
  try {
    await api.delete(`/api/admin/members/${confirmingDelete.value.id}`)
    toast.success(t('admin.deleted'))
    confirmingDelete.value = null
    await load()
  } catch {
    toast.error(t('admin.error_deleting'))
  } finally {
    deletingNow.value = false
  }
}

async function move(m, dir) {
  const i = members.value.findIndex(x => x.id === m.id)
  const j = i + dir
  if (j < 0 || j >= members.value.length) return
  const arr = [...members.value]
  ;[arr[i], arr[j]] = [arr[j], arr[i]]
  members.value = arr
  try {
    await api.post('/api/admin/members/reorder', { order: arr.map(x => x.id) })
  } catch {
    toast.error(t('admin.error_saving'))
    await load()
  }
}

function initials(name) {
  return (name || '?').split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
}

function resolveApiUrl(path) {
  if (!path) return ''
  if (/^https?:\/\//i.test(path)) return path
  const base = (api.defaults.baseURL || '').replace(/\/$/, '')
  if (!base) return path
  return path.startsWith('/') ? `${base}${path}` : `${base}/${path}`
}
</script>

<template>
  <div>
    <div class="flex items-center gap-3 mb-4">
      <button class="btn-primary" @click="newOne">{{ t('admin.new_member') }}</button>
    </div>

    <div v-if="loading" class="card p-6 text-center text-ink-500 italic font-sans">{{ t('admin.loading') }}</div>
    <div v-else-if="!members.length" class="card p-6 text-center text-ink-500 italic font-sans">{{ t('admin.no_results') }}</div>
    <ul v-else class="space-y-2">
      <li v-for="(m, i) in members" :key="m.id" class="card p-4 flex items-center gap-4">
        <img v-if="m.photo_url" :src="resolveApiUrl(m.photo_url)" :alt="m.full_name" class="w-12 h-12 rounded-full object-cover border border-gold-500/60" />
        <div v-else class="w-12 h-12 rounded-full bg-parchment-200 border border-gold-500/60 flex items-center justify-center font-serif text-ink-900 shrink-0">
          {{ initials(m.full_name) }}
        </div>
        <div class="flex-1 min-w-0">
          <div class="font-serif text-lg truncate">{{ m.full_name }}</div>
          <div v-if="m.is_instructor" class="text-xs text-gold-500 font-sans uppercase tracking-widest truncate">{{ t('admin.is_instructor') }}</div>
        </div>
        <div class="flex items-center gap-1 shrink-0">
          <button class="btn-ghost !px-2 !py-1 !text-[11px]" :disabled="i === 0" @click="move(m, -1)" :aria-label="t('admin.move_up')">↑</button>
          <button class="btn-ghost !px-2 !py-1 !text-[11px]" :disabled="i === members.length - 1" @click="move(m, 1)" :aria-label="t('admin.move_down')">↓</button>
          <button class="btn-ghost !px-2 !py-1 !text-[11px]" @click="edit(m)">{{ t('admin.edit') }}</button>
          <button class="btn-ghost !px-2 !py-1 !text-[11px] text-oxblood-500" @click="confirmingDelete = m">{{ t('admin.delete') }}</button>
        </div>
      </li>
    </ul>

    <!-- Edit modal -->
    <Teleport to="body">
      <div v-if="editing" class="fixed inset-0 bg-ink-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="card p-6 sm:p-7 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <h3 class="mb-5">{{ editing === 'new' ? t('admin.new_member') : t('admin.edit') }}</h3>

          <div class="grid sm:grid-cols-2 gap-4 font-sans text-sm">
            <label class="sm:col-span-2">
              <span class="field-label">{{ t('admin.full_name') }}</span>
              <input v-model="form.full_name" required class="field-input" />
            </label>
            <label class="sm:col-span-2">
              <span class="field-label">{{ t('admin.photo_url') }}</span>
              <input v-model="form.photo_url" type="url" class="field-input" placeholder="/img/members/name.png" />
            </label>
            <label class="sm:col-span-2">
              <span class="field-label">Upload photo</span>
              <input
                type="file"
                accept="image/*"
                class="field-input"
                :disabled="uploadingPhoto"
                @change="onPhotoFileChange"
              />
              <p v-if="uploadingPhoto" class="text-xs text-ink-500 mt-1">Uploading...</p>
            </label>
            <label class="flex items-center gap-2 mt-1">
              <input v-model="form.is_instructor" type="checkbox" />
              <span>{{ t('admin.is_instructor') }}</span>
            </label>
          </div>

          <div class="mt-6 flex gap-2 justify-end">
            <button class="btn-ghost" :disabled="saving" @click="cancel">{{ t('admin.cancel') }}</button>
            <button class="btn-primary" :disabled="saving" @click="save">
              <svg v-if="saving" class="w-4 h-4 mr-2 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25" />
                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
              </svg>
              {{ t('admin.save') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <ConfirmDialog
      :open="!!confirmingDelete"
      destructive
      :title="t('admin.delete')"
      :message="t('admin.confirm_delete_member')"
      :confirm-label="t('admin.delete')"
      :loading="deletingNow"
      @confirm="confirmDelete"
      @cancel="confirmingDelete = null"
    />
  </div>
</template>
