<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import api from '@/lib/api'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  slots: { type: Array, required: true },
})
const emit = defineEmits(['updated'])

const { t } = useI18n()
const auth = useAuthStore()
const toast = useToast()

const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']

const open = ref(false)
const saving = ref(false)
const draft = ref([])

function fillDraft() {
  draft.value = props.slots.map(s => ({ ...s }))
}
watch(() => props.slots, fillDraft, { immediate: true })

function openEditor() {
  fillDraft()
  open.value = true
}

function addSlot() {
  draft.value.push({ day: 'monday', start: '18:00', end: '20:00' })
}

function removeSlot(i) {
  draft.value.splice(i, 1)
}

async function save() {
  saving.value = true
  try {
    const { data } = await api.put('/api/admin/pages/regular-schedule', {
      title: {},
      body: { slots: draft.value },
      published: true,
    })
    emit('updated', data.data.body?.slots || draft.value)
    toast.success(t('admin.saved'))
    open.value = false
  } catch {
    toast.error(t('admin.error_saving'))
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <button
    v-if="auth.isAdmin"
    type="button"
    class="ml-2 inline-flex items-center justify-center w-7 h-7 rounded-full border border-gold-500/60 text-gold-500 hover:bg-gold-500/10 transition-colors"
    :title="t('admin.edit_schedule')"
    :aria-label="t('admin.edit_schedule')"
    @click="openEditor"
  >
    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 20h9" />
      <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z" />
    </svg>
  </button>

  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 bg-ink-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="card p-6 sm:p-7 max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <h3 class="mb-5">{{ t('admin.edit_schedule') }}</h3>

        <div class="space-y-3">
          <div
            v-for="(slot, i) in draft"
            :key="i"
            class="grid gap-2 items-center"
            style="grid-template-columns: 1fr auto auto auto auto"
          >
            <select v-model="slot.day" class="field-input !py-1.5 min-w-0">
              <option v-for="d in DAYS" :key="d" :value="d">
                {{ t(`schedule.${d}`) }}
              </option>
            </select>
            <input v-model="slot.start" type="time" class="field-input !py-1.5 w-24" />
            <span class="text-ink-500 text-sm">–</span>
            <input v-model="slot.end" type="time" class="field-input !py-1.5 w-24" />
            <button
              type="button"
              class="text-oxblood-500 hover:text-oxblood-700 transition-colors flex-shrink-0"
              :aria-label="t('admin.delete')"
              @click="removeSlot(i)"
            >
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6" /><path d="M19 6l-1 14H6L5 6" /><path d="M10 11v6" /><path d="M14 11v6" /><path d="M9 6V4h6v2" />
              </svg>
            </button>
          </div>
        </div>

        <button type="button" class="btn-ghost mt-4 text-sm" @click="addSlot">
          + {{ t('admin.add_slot') }}
        </button>

        <div class="mt-6 flex gap-2 justify-end">
          <button class="btn-ghost" :disabled="saving" @click="open = false">{{ t('admin.cancel') }}</button>
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
</template>
