<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'
import { useToast } from '@/composables/useToast'

const { t } = useI18n()
const toast = useToast()

const pages = [
  { slug: 'about', label: () => t('admin.page_about') },
  { slug: 'meyer', label: () => t('admin.page_meyer') },
  { slug: 'curriculum-intro', label: () => 'Curriculum — intro' },
  { slug: 'curriculum-i', label: () => 'Curriculum — I' },
  { slug: 'curriculum-ii', label: () => 'Curriculum — II' },
  { slug: 'curriculum-iii', label: () => 'Curriculum — III' },
  { slug: 'curriculum-iv', label: () => 'Curriculum — IV' },
  { slug: 'curriculum-v', label: () => 'Curriculum — V' },
  { slug: 'home-hero', label: () => 'Home — hero' },
  { slug: 'home-identity', label: () => 'Home — identity' },
  { slug: 'home-pillar-tradition', label: () => 'Home — pillar: tradition' },
  { slug: 'home-pillar-progression', label: () => 'Home — pillar: progression' },
  { slug: 'home-pillar-community', label: () => 'Home — pillar: community' },
  { slug: 'home-cta', label: () => 'Home — CTA' },
  { slug: 'home-cta-note', label: () => 'Home — CTA note' },
  { slug: 'contact-header', label: () => 'Contact — header' },
  { slug: 'contact-come', label: () => 'Contact — come block' },
  { slug: 'contact-follow', label: () => 'Contact — follow heading' },
  { slug: 'contact-form-intro', label: () => 'Contact — form intro' },
  { slug: 'members-header', label: () => 'Members — header' },
  { slug: 'members-fallback', label: () => 'Members — fallback note' },
  { slug: 'members-instructors', label: () => 'Members — instructors heading' },
  { slug: 'members-other', label: () => 'Members — other heading' },
  { slug: 'schedule-header', label: () => 'Schedule — header' },
  { slug: 'schedule-regular', label: () => 'Schedule — regular heading' },
  { slug: 'schedule-upcoming', label: () => 'Schedule — upcoming' },
]
const langs = ['lv', 'en', 'ru', 'cs', 'de']

const activeSlug = ref('')
const form = ref(null)
const loading = ref(false)
const saving = ref(false)

function blankForm(slug) {
  return {
    slug,
    title: { lv: '', en: '', ru: '', cs: '', de: '' },
    body: { lv: '', en: '', ru: '', cs: '', de: '' },
    published: true,
  }
}

async function loadPage(slug) {
  if (!slug) { form.value = null; return }
  loading.value = true
  try {
    const { data } = await api.get(`/api/admin/pages/${slug}`)
    const p = data.data
    form.value = {
      slug,
      title: { lv: '', en: '', ru: '', cs: '', de: '', ...(p.title || {}) },
      body: { lv: '', en: '', ru: '', cs: '', de: '', ...(p.body || {}) },
      published: p.published ?? true,
    }
  } catch {
    toast.error(t('admin.error_loading'))
    form.value = blankForm(slug)
  } finally {
    loading.value = false
  }
}

watch(activeSlug, (s) => loadPage(s))

async function save() {
  if (!form.value) return
  saving.value = true
  try {
    await api.put(`/api/admin/pages/${form.value.slug}`, {
      title: form.value.title,
      body: form.value.body,
      published: form.value.published,
    })
    toast.success(t('admin.saved'))
  } catch {
    toast.error(t('admin.error_saving'))
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div>
    <div class="flex flex-wrap items-center gap-2 mb-4">
      <button
        v-for="p in pages"
        :key="p.slug"
        @click="activeSlug = p.slug"
        :class="['px-3 py-1.5 border rounded-sm font-sans text-xs uppercase tracking-wider transition-colors',
          activeSlug === p.slug ? 'border-gold-500 text-gold-500 bg-gold-500/5' : 'border-parchment-300 text-ink-500 hover:border-ink-500/40']"
      >{{ p.label() }}</button>
    </div>

    <p v-if="!activeSlug" class="card p-6 text-center text-ink-500 italic font-sans">{{ t('admin.pick_page') }}</p>
    <div v-else-if="loading" class="card p-6 text-center text-ink-500 italic font-sans">{{ t('admin.loading') }}</div>
    <div v-else-if="form" class="card p-6 sm:p-7 space-y-5">
      <label class="flex items-center gap-2 font-sans text-sm">
        <input v-model="form.published" type="checkbox" />
        <span>{{ t('admin.page_published') }}</span>
      </label>

      <div>
        <h4 class="mb-2 uppercase text-xs tracking-widest text-ink-500 font-sans">{{ t('admin.page_title') }}</h4>
        <div class="grid sm:grid-cols-2 gap-2">
          <label v-for="l in langs" :key="'pt-'+l" class="text-xs font-sans">
            <span class="text-ink-500">{{ l.toUpperCase() }}</span>
            <input v-model="form.title[l]" class="field-input !py-2 mt-1" />
          </label>
        </div>
      </div>

      <div>
        <h4 class="mb-2 uppercase text-xs tracking-widest text-ink-500 font-sans">{{ t('admin.page_body') }}</h4>
        <div class="grid sm:grid-cols-2 gap-2">
          <label v-for="l in langs" :key="'pb-'+l" class="text-xs font-sans">
            <span class="text-ink-500">{{ l.toUpperCase() }}</span>
            <textarea v-model="form.body[l]" rows="6" class="field-input !py-2 mt-1"></textarea>
          </label>
        </div>
      </div>

      <div class="flex justify-end">
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
</template>
