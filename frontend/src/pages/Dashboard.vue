<script setup>
import { onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useTrainingsStore } from '@/stores/trainings'
import { useAuthStore } from '@/stores/auth'

const { t, locale } = useI18n()
const store = useTrainingsStore()
const auth = useAuthStore()

onMounted(() => store.fetchMine())

const fmt = computed(() => new Intl.DateTimeFormat(locale.value, {
  weekday: 'long', day: '2-digit', month: 'long', hour: '2-digit', minute: '2-digit',
}))
</script>

<template>
  <section class="max-w-3xl mx-auto px-4 py-16">
    <h1>{{ t('dashboard.title') }}</h1>
    <p class="text-ink-500 mt-1">{{ auth.user?.name }} — <span class="uppercase tracking-widest text-xs">{{ auth.user?.rank }}</span></p>
    <div class="divider-engraved my-6 w-1/3"></div>

    <div v-if="!store.mine.length" class="card p-8 text-center">
      <p class="italic text-ink-500">{{ t('dashboard.empty') }}</p>
      <router-link :to="{ name: 'schedule' }" class="btn-primary mt-4">{{ t('dashboard.browse') }}</router-link>
    </div>
    <ul v-else class="space-y-3">
      <li v-for="r in store.mine" :key="r.id" class="card p-4 flex justify-between items-center flex-wrap gap-2">
        <div>
          <div class="font-display text-xl">{{ r.training_session?.title?.[locale] || r.training_session?.focus }}</div>
          <div class="text-sm text-ink-500">{{ fmt.format(new Date(r.training_session.starts_at)) }}</div>
        </div>
        <span class="text-xs uppercase tracking-widest px-2 py-1 border"
          :class="r.status === 'confirmed' ? 'border-gold-500 text-gold-500' : 'border-oxblood-500 text-oxblood-500'">
          {{ t(`dashboard.status.${r.status}`) }}
        </span>
      </li>
    </ul>
  </section>
</template>
