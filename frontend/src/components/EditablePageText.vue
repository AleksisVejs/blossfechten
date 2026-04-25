<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import api from '@/lib/api'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  slug: { type: String, required: true },
  field: { type: String, default: 'body' }, // 'body' | 'title'
  page: { type: Object, default: null },
})
const emit = defineEmits(['updated'])

const { t } = useI18n()
const auth = useAuthStore()
const toast = useToast()

const langs = ['lv', 'en', 'ru', 'cs', 'de']
const open = ref(false)
const saving = ref(false)
const draft = ref({ lv: '', en: '', ru: '', cs: '', de: '' })

function fillDraft() {
  const src = props.page?.[props.field] || {}
  draft.value = { lv: '', en: '', ru: '', cs: '', de: '', ...src }
}
watch(() => [props.page, props.field], fillDraft, { immediate: true })

async function openEditor() {
  fillDraft()
  open.value = true
}

async function save() {
  saving.value = true
  try {
    const payload = {
      title: { ...(props.page?.title || {}) },
      body: { ...(props.page?.body || {}) },
      published: props.page?.published ?? true,
    }
    payload[props.field] = { ...draft.value }
    const { data } = await api.put(`/api/admin/pages/${props.slug}`, payload)
    emit('updated', data.data)
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
  <span class="relative inline-block align-middle">
    <button
      v-if="auth.isAdmin"
      type="button"
      class="ml-2 inline-flex items-center justify-center w-7 h-7 rounded-full border border-gold-500/60 text-gold-500 hover:bg-gold-500/10 transition-colors"
      :title="t('admin.edit')"
      :aria-label="t('admin.edit')"
      @click="openEditor"
    >
      <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 20h9" />
        <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z" />
      </svg>
    </button>

    <Teleport to="body">
      <div v-if="open" class="fixed inset-0 bg-ink-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="card p-6 sm:p-7 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <h3 class="mb-4">{{ t('admin.edit') }} — {{ slug }} / {{ field }}</h3>

          <div class="grid sm:grid-cols-2 gap-2">
            <label v-for="l in langs" :key="l" class="text-xs font-sans">
              <span class="text-ink-500">{{ l.toUpperCase() }}</span>
              <textarea
                v-if="field === 'body'"
                v-model="draft[l]"
                rows="6"
                class="field-input !py-2 mt-1"
              ></textarea>
              <input
                v-else
                v-model="draft[l]"
                class="field-input !py-2 mt-1"
              />
            </label>
          </div>

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
  </span>
</template>
