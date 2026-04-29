<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const token = String(route.query.token || '')
const form = ref({
  email: String(route.query.email || ''),
  password: '',
  password_confirmation: '',
})
const errorMsg = ref('')
const submitting = ref(false)
const done = ref(false)

useHead({
  title: () => `${t('auth.reset_title')} — Blossfechten Riga`,
  meta: [{ name: 'robots', content: 'noindex' }],
})

const passwordsMatch = computed(() =>
  form.value.password.length > 0 &&
  form.value.password === form.value.password_confirmation
)
const passwordsMismatch = computed(() =>
  form.value.password_confirmation.length > 0 && !passwordsMatch.value
)

async function submit() {
  errorMsg.value = ''
  submitting.value = true
  try {
    await auth.resetPassword({ ...form.value, token })
    done.value = true
    setTimeout(() => router.push({ name: 'login' }), 2000)
  } catch (e) {
    const errs = e.response?.data?.errors
    errorMsg.value = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || t('common.error'))
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <section class="max-w-md mx-auto px-4 py-10 sm:py-16">
    <div class="text-center mb-8">
      <img src="/img/logo.png" alt="Blossfechten Riga" class="w-14 h-14 mx-auto object-contain opacity-90" />
      <p class="uppercase tracking-[0.3em] text-[11px] text-gold-500 mt-4">Fencer's Guild Latvia</p>
      <h1 class="mt-2">{{ t('auth.reset_title') }}</h1>
      <div class="divider-engraved my-5 mx-auto w-1/3"></div>
      <p class="text-ink-500 italic text-sm">{{ t('auth.reset_subtitle') }}</p>
    </div>

    <form @submit.prevent="submit" class="card p-6 sm:p-8 space-y-5" novalidate>
      <div v-if="done" class="px-3 py-2 border border-gold-500/40 bg-gold-500/5 rounded-sm text-sm font-sans">
        {{ t('auth.reset_done') }}
      </div>
      <template v-else>
        <div v-if="!token" class="px-3 py-2 border border-oxblood-500/40 bg-oxblood-500/5 rounded-sm text-sm text-oxblood-500 font-sans">
          {{ t('auth.reset_invalid') }}
        </div>

        <div>
          <label for="reset-email" class="field-label">{{ t('auth.email') }}</label>
          <input id="reset-email" v-model="form.email" type="email" required class="field-input" autocomplete="email" />
        </div>

        <div>
          <label for="reset-password" class="field-label">{{ t('auth.password') }}</label>
          <input id="reset-password" v-model="form.password" type="password" required minlength="8" class="field-input" autocomplete="new-password" />
          <p class="text-xs text-ink-500/80 mt-1.5 font-sans">{{ t('auth.password_hint') }}</p>
        </div>

        <div>
          <label for="reset-confirm" class="field-label">{{ t('auth.password_confirmation') }}</label>
          <input id="reset-confirm" v-model="form.password_confirmation" type="password" required class="field-input" autocomplete="new-password" />
          <p v-if="passwordsMismatch" class="text-xs text-oxblood-500 mt-1.5 font-sans">{{ t('auth.password_mismatch') }}</p>
        </div>

        <div v-if="errorMsg" role="alert" class="px-3 py-2 border border-oxblood-500/40 bg-oxblood-500/5 rounded-sm text-sm text-oxblood-500 font-sans">
          {{ errorMsg }}
        </div>

        <button type="submit" class="btn-primary w-full" :disabled="submitting || passwordsMismatch || !token">
          {{ submitting ? t('auth.submitting') : t('auth.reset_submit') }}
        </button>
      </template>
    </form>
  </section>
</template>
