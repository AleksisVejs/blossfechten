<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const auth = useAuthStore()
const email = ref('')
const sent = ref(false)
const sending = ref(false)
const errorMsg = ref('')

useHead({
  title: () => `${t('auth.forgot_title')} — Blossfechten Riga`,
  meta: [{ name: 'robots', content: 'noindex' }],
})

async function submit() {
  errorMsg.value = ''
  sending.value = true
  try {
    await auth.forgotPassword(email.value)
    sent.value = true
  } catch (e) {
    errorMsg.value = e.response?.data?.message || t('common.error')
  } finally {
    sending.value = false
  }
}
</script>

<template>
  <section class="max-w-md mx-auto px-4 py-10 sm:py-16">
    <div class="text-center mb-8">
      <img src="/img/logo.png" alt="Blossfechten Riga" class="w-14 h-14 mx-auto object-contain opacity-90" />
      <p class="uppercase tracking-[0.3em] text-[11px] text-gold-500 mt-4">Fencer's Guild Latvia</p>
      <h1 class="mt-2">{{ t('auth.forgot_title') }}</h1>
      <div class="divider-engraved my-5 mx-auto w-1/3"></div>
      <p class="text-ink-500 italic text-sm">{{ t('auth.forgot_subtitle') }}</p>
    </div>

    <form @submit.prevent="submit" class="card p-6 sm:p-8 space-y-5" novalidate>
      <div v-if="sent" class="px-3 py-2 border border-gold-500/40 bg-gold-500/5 rounded-sm text-sm font-sans">
        {{ t('auth.forgot_sent') }}
      </div>
      <template v-else>
        <div>
          <label for="forgot-email" class="field-label">{{ t('auth.email') }}</label>
          <input id="forgot-email" v-model="email" type="email" required class="field-input" autocomplete="email" />
        </div>

        <div v-if="errorMsg" role="alert" class="px-3 py-2 border border-oxblood-500/40 bg-oxblood-500/5 rounded-sm text-sm text-oxblood-500 font-sans">
          {{ errorMsg }}
        </div>

        <button type="submit" class="btn-primary w-full" :disabled="sending">
          {{ sending ? t('auth.submitting') : t('auth.forgot_submit') }}
        </button>
      </template>
    </form>

    <p class="text-sm text-ink-500 text-center mt-6 font-sans">
      <router-link :to="{ name: 'login' }" class="text-oxblood-500 hover:text-oxblood-700 underline-offset-2 hover:underline">{{ t('nav.login') }}</router-link>
    </p>
  </section>
</template>
