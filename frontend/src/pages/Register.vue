<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
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

async function submit() {
  err.value = ''
  try {
    await auth.register(form.value)
    router.push({ name: 'dashboard' })
  } catch (e) {
    const errors = e.response?.data?.errors
    err.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || t('common.error'))
  }
}
</script>

<template>
  <section class="max-w-md mx-auto px-4 py-16">
    <h1 class="text-center">{{ t('auth.register_title') }}</h1>
    <div class="divider-engraved my-6 mx-auto w-1/3"></div>
    <form @submit.prevent="submit" class="card p-6 space-y-4 font-sans">
      <label class="block">
        <span class="text-sm text-ink-500">{{ t('auth.name') }}</span>
        <input v-model="form.name" required class="w-full mt-1 px-3 py-2 bg-parchment-50 border border-parchment-300 rounded-sm" />
      </label>
      <label class="block">
        <span class="text-sm text-ink-500">{{ t('auth.email') }}</span>
        <input v-model="form.email" type="email" required class="w-full mt-1 px-3 py-2 bg-parchment-50 border border-parchment-300 rounded-sm" />
      </label>
      <label class="block">
        <span class="text-sm text-ink-500">{{ t('auth.phone') }}</span>
        <input v-model="form.phone" class="w-full mt-1 px-3 py-2 bg-parchment-50 border border-parchment-300 rounded-sm" />
      </label>
      <label class="block">
        <span class="text-sm text-ink-500">{{ t('auth.password') }}</span>
        <input v-model="form.password" type="password" required minlength="8" class="w-full mt-1 px-3 py-2 bg-parchment-50 border border-parchment-300 rounded-sm" />
      </label>
      <label class="block">
        <span class="text-sm text-ink-500">{{ t('auth.password_confirmation') }}</span>
        <input v-model="form.password_confirmation" type="password" required class="w-full mt-1 px-3 py-2 bg-parchment-50 border border-parchment-300 rounded-sm" />
      </label>
      <label class="block">
        <span class="text-sm text-ink-500">{{ t('auth.choose_language') }}</span>
        <select v-model="form.locale" class="w-full mt-1 px-3 py-2 bg-parchment-50 border border-parchment-300 rounded-sm">
          <option v-for="l in SUPPORTED_LOCALES" :key="l.code" :value="l.code">{{ l.flag }} {{ l.label }}</option>
        </select>
      </label>
      <p v-if="err" class="text-oxblood-500 text-sm">{{ err }}</p>
      <button type="submit" class="btn-primary w-full" :disabled="auth.loading">{{ t('auth.submit_register') }}</button>
      <p class="text-sm text-ink-500 text-center">
        {{ t('auth.have_account') }}
        <router-link :to="{ name: 'login' }" class="underline hover:text-oxblood-500">{{ t('nav.login') }}</router-link>
      </p>
    </form>
  </section>
</template>
