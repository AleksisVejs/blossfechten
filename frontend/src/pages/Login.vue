<script setup>
import { ref, onMounted, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const form = ref({ email: '', password: '' })
const err = ref('')
const showPassword = ref(false)
const emailField = useTemplateRef('emailField')

useHead({
  title: () => `${t('auth.login_title')} — Blossfechten Riga`,
  meta: [
    { name: 'description', content: () => t('auth.login_subtitle') },
    { name: 'robots', content: 'noindex' },
  ],
})

onMounted(() => emailField.value?.focus())

async function submit() {
  err.value = ''
  try {
    await auth.login(form.value)
    router.push(route.query.next || { name: 'dashboard' })
  } catch (e) {
    err.value = e.response?.data?.message || t('common.error')
  }
}
</script>

<template>
  <section class="max-w-md mx-auto px-4 py-10 sm:py-16">
    <div class="text-center mb-8">
      <img src="/img/logo.png" alt="Blossfechten Riga" class="w-14 h-14 mx-auto object-contain opacity-90" />
      <p class="uppercase tracking-[0.3em] text-[11px] text-gold-500 mt-4">Fencer's Guild Latvia</p>
      <h1 class="mt-2">{{ t('auth.login_title') }}</h1>
      <div class="divider-engraved my-5 mx-auto w-1/3"></div>
      <p class="text-ink-500 italic text-sm">{{ t('auth.login_subtitle') }}</p>
    </div>

    <form @submit.prevent="submit" class="card p-6 sm:p-8 space-y-5" novalidate>
      <div>
        <label for="login-email" class="field-label">{{ t('auth.email') }}</label>
        <input
          id="login-email"
          ref="emailField"
          v-model="form.email"
          type="email"
          autocomplete="email"
          required
          :aria-invalid="!!err"
          class="field-input"
        />
      </div>

      <div>
        <div class="flex items-center justify-between mb-1.5">
          <label for="login-password" class="field-label !mb-0">{{ t('auth.password') }}</label>
          <button
            type="button"
            class="text-[11px] uppercase tracking-widest text-ink-500 hover:text-oxblood-500 font-sans"
            @click="showPassword = !showPassword"
          >{{ showPassword ? t('auth.hide_password') : t('auth.show_password') }}</button>
        </div>
        <input
          id="login-password"
          v-model="form.password"
          :type="showPassword ? 'text' : 'password'"
          autocomplete="current-password"
          required
          :aria-invalid="!!err"
          class="field-input"
        />
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
        :disabled="auth.loading"
      >
        <svg v-if="auth.loading" class="w-4 h-4 mr-2 animate-spin" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25" />
          <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
        </svg>
        {{ auth.loading ? t('auth.submitting') : t('auth.submit_login') }}
      </button>
    </form>

    <p class="text-sm text-ink-500 text-center mt-6 font-sans">
      {{ t('auth.no_account') }}
      <router-link :to="{ name: 'register' }" class="ml-1 text-oxblood-500 hover:text-oxblood-700 underline-offset-2 hover:underline">{{ t('nav.register') }}</router-link>
    </p>
    <p class="text-sm text-ink-500 text-center mt-2 font-sans">
      <router-link :to="{ name: 'forgot-password' }" class="text-oxblood-500 hover:text-oxblood-700 underline-offset-2 hover:underline">{{ t('auth.forgot_link') }}</router-link>
    </p>
  </section>
</template>
