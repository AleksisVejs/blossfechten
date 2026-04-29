<script setup>
import { ref, computed, onMounted, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { SUPPORTED_LOCALES } from '@/i18n'

const { t, locale } = useI18n()
const auth = useAuthStore()
const router = useRouter()
const form = ref({
  name: '', email: '', phone: '',
  password: '', password_confirmation: '',
  locale: locale.value,
})
const err = ref('')
const showPassword = ref(false)
const showConfirm = ref(false)
const nameField = useTemplateRef('nameField')

useHead({
  title: () => `${t('auth.register_title')} — Blossfechten Riga`,
  meta: [
    { name: 'description', content: () => t('auth.register_subtitle') },
    { name: 'robots', content: 'noindex' },
  ],
})

onMounted(() => nameField.value?.focus())

const passwordsMatch = computed(() =>
  form.value.password.length >= 8 &&
  form.value.password_confirmation.length > 0 &&
  form.value.password === form.value.password_confirmation
)
const passwordsMismatch = computed(() =>
  // Only show "mismatch" after user has entered a confirmation and the password meets min length.
  form.value.password_confirmation.length > 0 &&
  form.value.password.length >= 8 &&
  !passwordsMatch.value
)

async function submit() {
  err.value = ''
  try {
    await auth.register(form.value)
    router.push({ name: 'verify-email' })
  } catch (e) {
    const errors = e.response?.data?.errors
    err.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || t('common.error'))
  }
}
</script>

<template>
  <section class="max-w-md mx-auto px-4 py-10 sm:py-16">
    <div class="text-center mb-8">
      <img src="/img/logo.png" alt="Blossfechten Riga" class="w-14 h-14 mx-auto object-contain opacity-90" />
      <p class="uppercase tracking-[0.3em] text-[11px] text-gold-500 mt-4">Fencer's Guild Latvia</p>
      <h1 class="mt-2">{{ t('auth.register_title') }}</h1>
      <div class="divider-engraved my-5 mx-auto w-1/3"></div>
      <p class="text-ink-500 italic text-sm">{{ t('auth.register_subtitle') }}</p>
    </div>

    <form @submit.prevent="submit" class="card p-6 sm:p-8 space-y-5" novalidate>
      <div>
        <label for="reg-name" class="field-label">{{ t('auth.name') }}</label>
        <input
          id="reg-name"
          ref="nameField"
          v-model="form.name"
          autocomplete="name"
          required
          class="field-input"
        />
      </div>

      <div>
        <label for="reg-email" class="field-label">{{ t('auth.email') }}</label>
        <input
          id="reg-email"
          v-model="form.email"
          type="email"
          autocomplete="email"
          required
          class="field-input"
        />
      </div>

      <div>
        <label for="reg-phone" class="field-label">{{ t('auth.phone') }}</label>
        <input
          id="reg-phone"
          v-model="form.phone"
          type="tel"
          autocomplete="tel"
          inputmode="tel"
          placeholder="+371 …"
          class="field-input"
        />
      </div>

      <div>
        <div class="flex items-center justify-between mb-1.5">
          <label for="reg-password" class="field-label !mb-0">{{ t('auth.password') }}</label>
          <button
            type="button"
            class="text-[11px] uppercase tracking-widest text-ink-500 hover:text-oxblood-500 font-sans"
            @click="showPassword = !showPassword"
          >{{ showPassword ? t('auth.hide_password') : t('auth.show_password') }}</button>
        </div>
        <input
          id="reg-password"
          v-model="form.password"
          :type="showPassword ? 'text' : 'password'"
          autocomplete="new-password"
          required
          minlength="8"
          aria-describedby="reg-password-hint"
          class="field-input"
        />
        <p id="reg-password-hint" class="text-xs text-ink-500/80 mt-1.5 font-sans">{{ t('auth.password_hint') }}</p>
      </div>

      <div>
        <div class="flex items-center justify-between mb-1.5">
          <label for="reg-confirm" class="field-label !mb-0">{{ t('auth.password_confirmation') }}</label>
          <button
            type="button"
            class="text-[11px] uppercase tracking-widest text-ink-500 hover:text-oxblood-500 font-sans"
            @click="showConfirm = !showConfirm"
          >{{ showConfirm ? t('auth.hide_password') : t('auth.show_password') }}</button>
        </div>
        <input
          id="reg-confirm"
          v-model="form.password_confirmation"
          :type="showConfirm ? 'text' : 'password'"
          autocomplete="new-password"
          required
          :aria-invalid="passwordsMismatch"
          class="field-input"
        />
        <p v-if="passwordsMatch" class="text-xs text-gold-500 mt-1.5 font-sans flex items-center gap-1">
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12" />
          </svg>
          {{ t('auth.password_match') }}
        </p>
        <p v-else-if="passwordsMismatch" class="text-xs text-oxblood-500 mt-1.5 font-sans">
          {{ t('auth.password_mismatch') }}
        </p>
      </div>

      <div>
        <label for="reg-locale" class="field-label">{{ t('auth.choose_language') }}</label>
        <select id="reg-locale" v-model="form.locale" class="field-input">
          <option v-for="l in SUPPORTED_LOCALES" :key="l.code" :value="l.code">{{ l.flag }} {{ l.label }}</option>
        </select>
      </div>

      <div
        v-if="err"
        role="alert"
        class="flex items-start gap-2 px-3 py-2 border border-oxblood-500/40 bg-oxblood-500/5 rounded-sm text-sm text-oxblood-500 font-sans"
      >
        <svg class="w-4 h-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10" />
          <line x1="12" y1="8" x2="12" y2="12" />
          <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        <span>{{ err }}</span>
      </div>

      <button
        type="submit"
        class="btn-primary w-full"
        :disabled="auth.loading || !passwordsMatch"
      >
        <svg v-if="auth.loading" class="w-4 h-4 mr-2 animate-spin" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25" />
          <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
        </svg>
        {{ auth.loading ? t('auth.submitting') : t('auth.submit_register') }}
      </button>
    </form>

    <p class="text-sm text-ink-500 text-center mt-6 font-sans">
      {{ t('auth.have_account') }}
      <router-link :to="{ name: 'login' }" class="ml-1 text-oxblood-500 hover:text-oxblood-700 underline-offset-2 hover:underline">{{ t('nav.login') }}</router-link>
    </p>
  </section>
</template>
