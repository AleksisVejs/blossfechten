<script setup>
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import { useRoute } from 'vue-router'
import api from '@/lib/api'

const { t } = useI18n()
const route = useRoute()

const state = ref('working') // working | done | invalid
const email = ref('')

const token = computed(() => {
  const value = route.query.token
  return typeof value === 'string' ? value : ''
})

useHead({
  title: () => `${t('unsubscribe.title')} — Blossfechten Riga`,
  meta: [{ name: 'robots', content: 'noindex' }],
})

onMounted(async () => {
  if (!token.value) {
    state.value = 'invalid'
    return
  }

  try {
    const { data } = await api.post(`/api/notifications/unsubscribe/${encodeURIComponent(token.value)}`)
    email.value = data?.email || ''
    state.value = 'done'
  } catch {
    state.value = 'invalid'
  }
})
</script>

<template>
  <section class="max-w-xl mx-auto px-4 py-16 text-center">
    <h1>{{ t('unsubscribe.title') }}</h1>
    <div class="divider-engraved my-6 mx-auto w-1/3"></div>

    <p v-if="state === 'working'" class="font-sans text-ink-500">
      {{ t('unsubscribe.working') }}
    </p>

    <template v-else-if="state === 'done'">
      <p class="font-sans text-ink-900">{{ t('unsubscribe.done') }}</p>
      <p v-if="email" class="font-sans text-sm text-ink-500 mt-2">{{ email }}</p>
      <p class="font-sans text-sm text-ink-500 mt-4">{{ t('unsubscribe.resubscribe_hint') }}</p>
      <RouterLink to="/profile" class="btn-primary inline-block mt-5">
        {{ t('unsubscribe.to_profile') }}
      </RouterLink>
    </template>

    <template v-else>
      <p class="font-sans text-oxblood-500">{{ t('unsubscribe.invalid') }}</p>
      <RouterLink to="/" class="btn-primary inline-block mt-5">
        {{ t('unsubscribe.to_home') }}
      </RouterLink>
    </template>
  </section>
</template>
