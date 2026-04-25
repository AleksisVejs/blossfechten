<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { SUPPORTED_LOCALES, setLocale } from '@/i18n'
import { useToast } from '@/composables/useToast'

const { t } = useI18n()
const auth = useAuthStore()
const toast = useToast()
const err = ref('')
const form = ref({
  name: auth.user?.name || '',
  phone: auth.user?.phone || '',
  locale: auth.user?.locale || 'en',
})

const userInitial = computed(() => {
  const name = auth.user?.name || ''
  return name.trim().charAt(0).toUpperCase() || '?'
})

watch(
  () => auth.user,
  (user) => {
    form.value = {
      name: user?.name || '',
      phone: user?.phone || '',
      locale: user?.locale || 'en',
    }
  },
  { immediate: true },
)

async function saveProfile() {
  err.value = ''
  try {
    await auth.updateProfile(form.value)
    setLocale(form.value.locale)
    toast.success(t('profile.saved'))
  } catch (e) {
    const errors = e.response?.data?.errors
    err.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || t('common.error'))
  }
}
</script>

<template>
  <section class="max-w-3xl mx-auto px-4 py-10 sm:py-16">
    <h1>{{ t('profile.title') }}</h1>
    <p class="text-ink-500 mt-1">{{ t('profile.subtitle') }}</p>
    <div class="divider-engraved my-6 w-1/3"></div>

    <form class="card p-6 sm:p-8" @submit.prevent="saveProfile">
      <div class="flex items-center gap-4">
        <div class="h-14 w-14 rounded-full bg-oxblood-500 text-white flex items-center justify-center text-xl font-semibold">
          {{ userInitial }}
        </div>
        <div>
          <h2 class="text-2xl !m-0">{{ auth.user?.name || t('profile.not_set') }}</h2>
          <p class="text-sm uppercase tracking-widest text-ink-500 mt-1">
            {{ auth.user?.rank || t('profile.not_set') }}
          </p>
        </div>
      </div>

      <dl class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-lg border border-parchment-300/70 bg-parchment-100/70 p-4">
          <label for="profile-name" class="field-label !mb-1 text-xs uppercase tracking-widest text-ink-500">{{ t('profile.full_name') }}</label>
          <input id="profile-name" v-model="form.name" class="field-input" required maxlength="120" />
        </div>
        <div class="rounded-lg border border-parchment-300/70 bg-parchment-100/70 p-4">
          <dt class="text-xs uppercase tracking-widest text-ink-500">{{ t('profile.email') }}</dt>
          <dd class="mt-1 text-ink-900">{{ auth.user?.email || t('profile.not_set') }}</dd>
        </div>
        <div class="rounded-lg border border-parchment-300/70 bg-parchment-100/70 p-4">
          <label for="profile-phone" class="field-label !mb-1 text-xs uppercase tracking-widest text-ink-500">{{ t('profile.phone') }}</label>
          <input id="profile-phone" v-model="form.phone" class="field-input" type="tel" inputmode="tel" maxlength="32" />
        </div>
        <div class="rounded-lg border border-parchment-300/70 bg-parchment-100/70 p-4">
          <label for="profile-locale" class="field-label !mb-1 text-xs uppercase tracking-widest text-ink-500">{{ t('profile.language') }}</label>
          <select id="profile-locale" v-model="form.locale" class="field-input">
            <option v-for="l in SUPPORTED_LOCALES" :key="l.code" :value="l.code">{{ l.flag }} {{ l.label }}</option>
          </select>
        </div>
      </dl>

      <div class="mt-4 rounded-lg border border-parchment-300/70 bg-parchment-100/70 p-4">
        <dt class="text-xs uppercase tracking-widest text-ink-500">{{ t('profile.role') }}</dt>
        <dd class="mt-1 text-ink-900">{{ auth.user?.role || t('profile.not_set') }}</dd>
      </div>

      <div
        v-if="err"
        role="alert"
        class="mt-4 flex items-start gap-2 px-3 py-2 border border-oxblood-500/40 bg-oxblood-500/5 rounded-sm text-sm text-oxblood-500 font-sans"
      >
        <span>{{ err }}</span>
      </div>

      <div class="mt-5 flex justify-end">
        <button type="submit" class="btn-primary" :disabled="auth.loading">
          {{ auth.loading ? t('auth.submitting') : t('profile.save') }}
        </button>
      </div>
    </form>
  </section>
</template>
