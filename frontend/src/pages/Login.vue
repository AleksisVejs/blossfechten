<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const form = ref({ email: '', password: '' })
const err = ref('')

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
  <section class="max-w-md mx-auto px-4 py-16">
    <h1 class="text-center">{{ t('auth.login_title') }}</h1>
    <div class="divider-engraved my-6 mx-auto w-1/3"></div>
    <form @submit.prevent="submit" class="card p-6 space-y-4 font-sans">
      <label class="block">
        <span class="text-sm text-ink-500">{{ t('auth.email') }}</span>
        <input v-model="form.email" type="email" required class="w-full mt-1 px-3 py-2 bg-parchment-50 border border-parchment-300 rounded-sm" />
      </label>
      <label class="block">
        <span class="text-sm text-ink-500">{{ t('auth.password') }}</span>
        <input v-model="form.password" type="password" required class="w-full mt-1 px-3 py-2 bg-parchment-50 border border-parchment-300 rounded-sm" />
      </label>
      <p v-if="err" class="text-oxblood-500 text-sm">{{ err }}</p>
      <button type="submit" class="btn-primary w-full" :disabled="auth.loading">{{ t('auth.submit_login') }}</button>
      <p class="text-sm text-ink-500 text-center">
        {{ t('auth.no_account') }}
        <router-link :to="{ name: 'register' }" class="underline hover:text-oxblood-500">{{ t('nav.register') }}</router-link>
      </p>
    </form>
  </section>
</template>
