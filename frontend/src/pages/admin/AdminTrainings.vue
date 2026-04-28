<script setup>
import { onMounted, ref, computed, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'
import { formatDateTimeLocal, parseLocalDateTime } from '@/lib/datetime'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/ConfirmDialog.vue'

const { t } = useI18n()
const toast = useToast()

const trainings = ref([])
const loading = ref(false)
const editing = ref(null) // 'new' | id | null
const form = ref(emptyForm())
const formParts = reactive({
  starts_at_date: '',
  starts_at_time: '',
  ends_at_date: '',
  ends_at_time: '',
})
const filter = ref('upcoming') // upcoming | past | cancelled | all
const search = ref('')
const saving = ref(false)

const confirmingDelete = ref(null)
const deletingNow = ref(false)

const registrations = ref(null) // { training, items[] } | null
const loadingRegs = ref(false)

const fmt = computed(() => new Intl.DateTimeFormat('en-GB', {
  day: '2-digit',
  month: '2-digit',
  year: 'numeric',
  hour: '2-digit',
  minute: '2-digit',
  hour12: false,
}))
const langs = ['lv', 'en', 'ru', 'cs', 'de']

function getDatePart(value) {
  return typeof value === 'string' && value.includes('T') ? value.split('T')[0] : ''
}

function getTimePart(value) {
  if (typeof value !== 'string' || !value.includes('T')) return ''
  return value.split('T')[1]?.slice(0, 5) || ''
}

function syncFormParts() {
  formParts.starts_at_date = getDatePart(form.value.starts_at)
  formParts.starts_at_time = getTimePart(form.value.starts_at)
  formParts.ends_at_date = getDatePart(form.value.ends_at)
  formParts.ends_at_time = getTimePart(form.value.ends_at)
}

function normalizeTimeInput(value) {
  if (typeof value !== 'string') return ''

  const cleaned = value.replace(/\D/g, '')
  if (!cleaned) return ''

  if (cleaned.length <= 2) return cleaned
  if (cleaned.length <= 4) return `${cleaned.slice(0, 2)}:${cleaned.slice(2)}`

  return null
}

function isValidTimeInput(value) {
  if (!/^\d{2}:\d{2}$/.test(value)) return false
  const [hours, minutes] = value.split(':').map(Number)
  return hours >= 0 && hours <= 23 && minutes >= 0 && minutes <= 59
}

function updateDatePart(field, value) {
  formParts[`${field}_date`] = value
}

function updateTimePart(field, value) {
  const normalized = normalizeTimeInput(value)
  if (normalized === null) return
  formParts[`${field}_time`] = normalized
}

function buildDateTime(field) {
  const datePart = formParts[`${field}_date`]
  const timePart = formParts[`${field}_time`]

  if (!datePart || !isValidTimeInput(timePart)) return ''

  return `${datePart}T${timePart}`
}

function formatTrainingDateRange(session) {
  const start = parseLocalDateTime(session.starts_at)
  const end = parseLocalDateTime(session.ends_at)
  if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) return ''

  const isSameDay =
    start.getFullYear() === end.getFullYear()
    && start.getMonth() === end.getMonth()
    && start.getDate() === end.getDate()

  if (isSameDay) {
    return fmt.value.format(start)
  }

  return `${fmt.value.format(start)} - ${fmt.value.format(end)}`
}

function emptyForm() {
  const d = new Date()
  return {
    id: null,
    starts_at: formatDateTimeLocal(d),
    ends_at: formatDateTimeLocal(new Date(d.getTime() + 2 * 3600 * 1000)),
    location: 'Ādmiņu iela 4, Rīga',
    focus: 'Longsword',
    title: { lv: '', en: '', ru: '', cs: '', de: '' },
    description: { lv: '', en: '', ru: '', cs: '', de: '' },
    capacity: 20,
    cancelled: false,
  }
}

syncFormParts()

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/api/admin/trainings')
    trainings.value = data.data.data
  } catch {
    toast.error(t('admin.error_loading'))
  } finally {
    loading.value = false
  }
}
onMounted(load)

const filtered = computed(() => {
  const now = new Date()
  const q = search.value.trim().toLowerCase()
  return trainings.value.filter(s => {
    if (filter.value === 'upcoming' && (s.cancelled || parseLocalDateTime(s.ends_at) < now)) return false
    if (filter.value === 'past' && parseLocalDateTime(s.ends_at) >= now) return false
    if (filter.value === 'cancelled' && !s.cancelled) return false
    if (q) {
      const hay = [s.focus, s.location, ...Object.values(s.title || {}), ...Object.values(s.description || {})]
        .filter(Boolean).join(' ').toLowerCase()
      if (!hay.includes(q)) return false
    }
    return true
  })
})

const registrationsSummary = computed(() => {
  const sessionsWithRegistrations = trainings.value.filter((s) => (s.registrations_count ?? 0) > 0).length
  const totalRegistrations = trainings.value.reduce((sum, s) => sum + (s.registrations_count ?? 0), 0)
  return { sessionsWithRegistrations, totalRegistrations }
})

function edit(row) {
  editing.value = row.id
  form.value = {
    ...row,
    starts_at: formatDateTimeLocal(row.starts_at),
    ends_at: formatDateTimeLocal(row.ends_at),
    title: { lv: '', en: '', ru: '', cs: '', de: '', ...(row.title || {}) },
    description: { lv: '', en: '', ru: '', cs: '', de: '', ...(row.description || {}) },
  }
  syncFormParts()
}
function newOne() {
  editing.value = 'new'
  form.value = emptyForm()
  syncFormParts()
}
function cancel() { editing.value = null }

async function save() {
  saving.value = true
  try {
    const payload = {
      ...form.value,
      starts_at: buildDateTime('starts_at'),
      ends_at: buildDateTime('ends_at'),
      members_only: false,
    }

    if (!payload.starts_at || !payload.ends_at) {
      throw new Error('invalid-datetime')
    }

    if (editing.value === 'new') await api.post('/api/admin/trainings', payload)
    else await api.put(`/api/admin/trainings/${editing.value}`, payload)
    editing.value = null
    toast.success(t('admin.saved'))
    await load()
  } catch (e) {
    toast.error(e.message === 'invalid-datetime'
      ? 'Please enter a valid date and time in HH:mm format.'
      : (e.response?.data?.message || t('admin.error_saving')))
  } finally {
    saving.value = false
  }
}

async function confirmDelete() {
  if (!confirmingDelete.value) return
  deletingNow.value = true
  try {
    await api.delete(`/api/admin/trainings/${confirmingDelete.value.id}`)
    toast.success(t('admin.deleted'))
    confirmingDelete.value = null
    await load()
  } catch {
    toast.error(t('admin.error_deleting'))
  } finally {
    deletingNow.value = false
  }
}

async function viewRegistrations(row) {
  loadingRegs.value = true
  registrations.value = { training: row, items: [] }
  try {
    const { data } = await api.get(`/api/admin/trainings/${row.id}/registrations`)
    registrations.value.items = data.data
  } catch {
    toast.error(t('admin.error_loading'))
  } finally {
    loadingRegs.value = false
  }
}
</script>

<template>
  <div>
    <div class="flex flex-wrap items-center gap-3 mb-4">
      <button class="btn-primary" @click="newOne">{{ t('admin.new_training') }}</button>
      <div class="flex flex-wrap gap-1 font-sans text-xs uppercase tracking-wider ml-auto">
        <button v-for="f in ['upcoming','past','cancelled','all']" :key="f"
          @click="filter = f"
          :class="['px-3 py-1.5 border rounded-sm transition-colors', filter === f ? 'border-gold-500 text-gold-500 bg-gold-500/5' : 'border-parchment-300 text-ink-500 hover:border-ink-500/40']">
          {{ t(`admin.filter_${f}`) }}
        </button>
      </div>
      <input
        v-model="search"
        type="search"
        :placeholder="t('admin.search')"
        class="field-input !w-auto !py-1.5 text-sm md:min-w-[14rem]"
      />
    </div>

    <div class="card p-4 mb-4 border-l-4 border-gold-500 bg-gold-500/5">
      <div class="flex flex-wrap items-center gap-3 text-sm font-sans">
        <span class="uppercase tracking-wider text-ink-500">{{ t('admin.registrations') }}</span>
        <span class="text-ink-900 font-semibold">{{ registrationsSummary.totalRegistrations }}</span>
        <span class="text-ink-500">{{ t('admin.across') }}</span>
        <span class="text-ink-900 font-semibold">{{ registrationsSummary.sessionsWithRegistrations }}</span>
        <span class="text-ink-500">{{ t('admin.training_sessions') }}</span>
      </div>
    </div>

    <div v-if="loading" class="card p-6 text-center text-ink-500 italic font-sans">{{ t('admin.loading') }}</div>
    <div v-else-if="!filtered.length" class="card p-6 text-center text-ink-500 italic font-sans">{{ t('admin.no_results') }}</div>
    <div v-else class="card overflow-x-auto">
      <table class="w-full text-sm font-sans">
        <thead class="bg-parchment-200/60 text-left">
          <tr>
            <th class="p-3">{{ t('admin.date') }}</th>
            <th class="p-3">{{ t('admin.focus') }}</th>
            <th class="p-3 hidden md:table-cell">{{ t('admin.location') }}</th>
            <th class="p-3">{{ t('admin.capacity') }}</th>
            <th class="p-3">{{ t('admin.registrations') }}</th>
            <th class="p-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="s in filtered"
            :key="s.id"
            :class="[
              'border-t border-parchment-300/50',
              s.cancelled && 'opacity-60',
              (s.registrations_count ?? 0) > 0 && 'bg-gold-500/5',
            ]"
          >
            <td class="p-3 whitespace-nowrap">
              {{ formatTrainingDateRange(s) }}
              <span v-if="s.cancelled" class="ml-2 text-[10px] uppercase tracking-widest text-oxblood-500">{{ t('admin.cancelled') }}</span>
            </td>
            <td class="p-3">{{ s.focus }}</td>
            <td class="p-3 hidden md:table-cell text-ink-500">{{ s.location }}</td>
            <td class="p-3">{{ s.capacity }}</td>
            <td class="p-3">
              <button
                class="btn-primary !px-3 !py-1 !text-[11px]"
                @click="viewRegistrations(s)"
              >
                {{ t('admin.registrations') }}: {{ s.registrations_count ?? 0 }}
              </button>
            </td>
            <td class="p-3 text-right space-x-1 whitespace-nowrap">
              <button class="btn-ghost !px-2 !py-1 !text-[11px]" @click="edit(s)">{{ t('admin.edit') }}</button>
              <button class="btn-ghost !px-2 !py-1 !text-[11px] text-oxblood-500" @click="confirmingDelete = s">{{ t('admin.delete') }}</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Edit / new modal -->
    <Teleport to="body">
      <div v-if="editing" class="fixed inset-0 bg-ink-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="card p-6 sm:p-7 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <h3 class="mb-5">{{ editing === 'new' ? t('admin.new_training') : t('admin.edit') }}</h3>

          <div class="grid sm:grid-cols-2 gap-4 font-sans text-sm">
            <label>
              <span class="field-label">Start date</span>
              <input
                :value="formParts.starts_at_date"
                type="date"
                class="field-input"
                @input="updateDatePart('starts_at', $event.target.value)"
              />
            </label>
            <label>
              <span class="field-label">Start time</span>
              <input
                :value="formParts.starts_at_time"
                type="text"
                class="field-input"
                inputmode="numeric"
                placeholder="HH:mm"
                maxlength="5"
                pattern="[0-2][0-9]:[0-5][0-9]"
                @input="updateTimePart('starts_at', $event.target.value)"
              />
            </label>
            <label>
              <span class="field-label">End date</span>
              <input
                :value="formParts.ends_at_date"
                type="date"
                class="field-input"
                @input="updateDatePart('ends_at', $event.target.value)"
              />
            </label>
            <label>
              <span class="field-label">End time</span>
              <input
                :value="formParts.ends_at_time"
                type="text"
                class="field-input"
                inputmode="numeric"
                placeholder="HH:mm"
                maxlength="5"
                pattern="[0-2][0-9]:[0-5][0-9]"
                @input="updateTimePart('ends_at', $event.target.value)"
              />
            </label>
            <label>
              <span class="field-label">{{ t('admin.focus') }}</span>
              <input v-model="form.focus" class="field-input" />
            </label>
            <label>
              <span class="field-label">{{ t('admin.location') }}</span>
              <input v-model="form.location" class="field-input" />
            </label>
            <label>
              <span class="field-label">{{ t('admin.capacity') }}</span>
              <input v-model.number="form.capacity" type="number" min="1" max="200" class="field-input" />
            </label>
            <div class="flex items-center gap-4 mt-4">
              <label class="flex items-center gap-2 font-sans text-sm">
                <input v-model="form.cancelled" type="checkbox" /> {{ t('admin.cancelled') }}
              </label>
            </div>
          </div>

          <h4 class="mt-6 mb-2 uppercase text-xs tracking-widest text-ink-500 font-sans">Title (per language)</h4>
          <div class="grid sm:grid-cols-2 gap-2">
            <label v-for="l in langs" :key="'t-'+l" class="text-xs font-sans">
              <span class="text-ink-500">{{ l.toUpperCase() }}</span>
              <input v-model="form.title[l]" class="field-input !py-2 mt-1" />
            </label>
          </div>

          <h4 class="mt-5 mb-2 uppercase text-xs tracking-widest text-ink-500 font-sans">Description</h4>
          <div class="grid sm:grid-cols-2 gap-2">
            <label v-for="l in langs" :key="'d-'+l" class="text-xs font-sans">
              <span class="text-ink-500">{{ l.toUpperCase() }}</span>
              <textarea v-model="form.description[l]" rows="2" class="field-input !py-2 mt-1"></textarea>
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

    <!-- Registrations modal -->
    <Teleport to="body">
      <div v-if="registrations" class="fixed inset-0 bg-ink-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="registrations = null">
        <div class="card p-6 sm:p-7 max-w-xl w-full max-h-[90vh] overflow-y-auto">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h3 class="mb-1">{{ t('admin.registrations') }}</h3>
              <p class="text-xs text-ink-500 font-sans">
                {{ formatTrainingDateRange(registrations.training) }} · {{ registrations.training.focus }}
              </p>
            </div>
            <button class="text-2xl leading-none text-ink-500 hover:text-ink-900" @click="registrations = null">×</button>
          </div>
          <div v-if="loadingRegs" class="text-center text-ink-500 italic font-sans py-8">{{ t('admin.loading') }}</div>
          <div v-else-if="!registrations.items.length" class="text-center text-ink-500 italic font-sans py-6">{{ t('admin.no_registrations') }}</div>
          <table v-else class="w-full text-sm font-sans">
            <thead class="text-left text-xs uppercase tracking-widest text-ink-500">
              <tr><th class="py-2">{{ t('admin.name') }}</th><th class="py-2">{{ t('admin.email') }}</th><th class="py-2">{{ t('admin.status') }}</th></tr>
            </thead>
            <tbody>
              <tr v-for="r in registrations.items" :key="r.id" class="border-t border-parchment-300/50">
                <td class="py-2">
                  <div>{{ r.user?.name }}</div>
                  <div v-if="r.user?.rank" class="text-[11px] uppercase tracking-widest text-gold-500">{{ r.user.rank }}</div>
                </td>
                <td class="py-2 text-ink-500 break-all">
                  <a :href="`mailto:${r.user?.email}`" class="hover:text-oxblood-500">{{ r.user?.email }}</a>
                  <div v-if="r.user?.phone" class="text-[11px]"><a :href="`tel:${r.user.phone}`" class="hover:text-oxblood-500">{{ r.user.phone }}</a></div>
                </td>
                <td class="py-2">
                  <span :class="r.status === 'confirmed' ? 'text-gold-500' : 'text-oxblood-500'" class="text-xs uppercase tracking-widest">{{ t(`admin.${r.status}`, r.status) }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Teleport>

    <ConfirmDialog
      :open="!!confirmingDelete"
      destructive
      :title="t('admin.delete')"
      :message="t('admin.confirm_delete_training')"
      :confirm-label="t('admin.delete')"
      :loading="deletingNow"
      @confirm="confirmDelete"
      @cancel="confirmingDelete = null"
    />
  </div>
</template>
