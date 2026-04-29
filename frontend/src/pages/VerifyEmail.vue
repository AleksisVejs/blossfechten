<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const route = useRoute()
const auth = useAuthStore()

const sending = ref(false)
const sent = ref(false)
const errorMsg = ref('')

const status = computed(() => route.query.status || '')

useHead({
  title: () => `${t('auth.verify_title')} — Blossfechten Riga`,
  meta: [{ name: 'robots', content: 'noindex' }],
})

onMounted(() => {
  if (!auth.initialized) auth.initSession()
})

async function resend() {
  sending.value = true
  errorMsg.value = ''
  sent.value = false
  try {
    await auth.resendVerification()
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
      <h1 class="mt-2">{{ t('auth.verify_title') }}</h1>
      <div class="divider-engraved my-5 mx-auto w-1/3"></div>
    </div>

    <div class="card p-6 sm:p-8 space-y-5">
      <div v-if="status === 'success'" class="px-3 py-2 border border-gold-500/40 bg-gold-500/5 rounded-sm text-sm font-sans">
        {{ t('auth.verify_success') }}
      </div>
      <div v-else-if="status === 'already'" class="px-3 py-2 border border-gold-500/40 bg-gold-500/5 rounded-sm text-sm font-sans">
        {{ t('auth.verify_already') }}
      </div>
      <div v-else-if="status === 'invalid'" class="px-3 py-2 border border-oxblood-500/40 bg-oxblood-500/5 rounded-sm text-sm text-oxblood-500 font-sans">
        {{ t('auth.verify_invalid') }}
      </div>
      <p v-else class="text-ink-500 text-sm font-sans">{{ t('auth.verify_pending') }}</p>

      <div v-if="auth.isAuthenticated && !auth.user?.email_verified_at" class="pt-3 border-t border-parchment-700/30">
        <p class="text-sm text-ink-500 mb-3 font-sans">{{ t('auth.verify_resend_hint') }}</p>
        <button class="btn-primary w-full" :disabled="sending" @click="resend">
          {{ sending ? t('auth.submitting') : t('auth.verify_resend') }}
        </button>
        <p v-if="sent" class="text-xs text-gold-500 mt-2 font-sans">{{ t('auth.verify_resent') }}</p>
        <p v-if="errorMsg" class="text-xs text-oxblood-500 mt-2 font-sans">{{ errorMsg }}</p>
      </div>

      <p class="text-sm text-ink-500 text-center pt-2 font-sans">
        <router-link :to="{ name: 'login' }" class="text-oxblood-500 hover:text-oxblood-700 underline-offset-2 hover:underline">{{ t('nav.login') }}</router-link>
      </p>
    </div>
  </section>
</template>
