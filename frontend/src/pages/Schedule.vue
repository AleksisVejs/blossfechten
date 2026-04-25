<script setup>
import { onMounted, ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import { useTrainingsStore } from '@/stores/trainings'
import TrainingCard from '@/components/TrainingCard.vue'
import StructuredData from '@/components/StructuredData.vue'

const { t, locale } = useI18n()
const store = useTrainingsStore()
const message = ref('')

useHead({
  title: () => `${t('schedule.title')} — Blossfechten Riga`,
  meta: [
    { name: 'description', content: () => `${t('schedule.upcoming')} — Blossfechten Riga. ${t('schedule.regular')}: ${t('schedule.monday')}, ${t('schedule.wednesday')}, ${t('schedule.saturday')}.` },
    { property: 'og:title', content: () => `${t('schedule.title')} — Blossfechten Riga` },
  ],
})

const eventsSchema = computed(() => ({
  '@context': 'https://schema.org',
  '@graph': store.list.map(s => ({
    '@type': 'Event',
    name: s.title?.[locale.value] || s.title?.en || s.focus || 'Blossfechten Riga Training',
    startDate: s.starts_at,
    endDate: s.ends_at,
    location: {
      '@type': 'Place',
      name: s.location || 'Riga',
      address: { '@type': 'PostalAddress', addressLocality: 'Riga', addressCountry: 'LV' },
    },
    organizer: { '@type': 'Organization', name: 'Blossfechten Riga' },
    eventStatus: s.cancelled ? 'https://schema.org/EventCancelled' : 'https://schema.org/EventScheduled',
  })),
}))

onMounted(() => store.fetch())

async function onRegister(s) {
  try {
    await store.register(s.id)
    message.value = t('schedule.register') + ' ✓'
  } catch (e) { message.value = e.response?.data?.message || t('common.error') }
}
async function onUnregister(s) {
  try { await store.unregister(s.id); message.value = t('schedule.unregister') + ' ✓' }
  catch { message.value = t('common.error') }
}
</script>

<template>
  <div>
  <StructuredData v-if="store.list.length" :schema="eventsSchema" />
  <section class="max-w-5xl mx-auto px-4 py-16">
    <h1 class="text-center">{{ t('schedule.title') }}</h1>
    <div class="divider-engraved my-6 mx-auto w-1/3"></div>

    <div class="card p-6 mb-12">
      <h2 class="mb-4">{{ t('schedule.regular') }}</h2>
      <ul class="grid sm:grid-cols-2 md:grid-cols-3 gap-4 font-sans">
        <li class="border-l-2 border-gold-500 pl-3">
          <div class="uppercase tracking-widest text-xs text-ink-500">{{ t('schedule.monday') }}</div>
          <div class="text-xl">18:00 – 20:00</div>
        </li>
        <li class="border-l-2 border-gold-500 pl-3">
          <div class="uppercase tracking-widest text-xs text-ink-500">{{ t('schedule.wednesday') }}</div>
          <div class="text-xl">18:00 – 20:00</div>
        </li>
        <li class="border-l-2 border-gold-500 pl-3">
          <div class="uppercase tracking-widest text-xs text-ink-500">{{ t('schedule.saturday') }}</div>
          <div class="text-xl">11:00 – 14:00</div>
        </li>
      </ul>
    </div>

    <h2 class="mb-4">{{ t('schedule.upcoming') }}</h2>
    <p v-if="message" class="mb-4 text-oxblood-500 font-sans">{{ message }}</p>

    <div v-if="store.loading">{{ t('common.loading') }}</div>
    <div v-else-if="!store.list.length" class="text-ink-500 italic">{{ t('schedule.no_upcoming') }}</div>
    <div v-else class="grid md:grid-cols-2 gap-4">
      <TrainingCard v-for="s in store.list" :key="s.id" :session="s"
        @register="onRegister" @unregister="onUnregister" />
    </div>
  </section>
  </div>
</template>
