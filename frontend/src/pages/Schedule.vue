<script setup>
import { onMounted, ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import { useTrainingsStore } from '@/stores/trainings'
import TrainingCard from '@/components/TrainingCard.vue'
import StructuredData from '@/components/StructuredData.vue'
import EditableScheduleSlots from '@/components/EditableScheduleSlots.vue'
import api from '@/lib/api'

const { t, locale } = useI18n()
const store = useTrainingsStore()
const message = ref('')

useHead({
  title: () => `${t('schedule.title')} — Blossfechten Riga`,
  meta: [
    { name: 'description', content: () => `${t('schedule.upcoming')} — Blossfechten Riga. ${t('schedule.regular')}: ${t('schedule.monday')}, ${t('schedule.wednesday')}, ${t('schedule.friday')}, ${t('schedule.saturday')}, ${t('schedule.sunday')}.` },
    { property: 'og:title', content: () => `${t('schedule.title')} — Blossfechten Riga` },
  ],
})

const DEFAULT_SLOTS = [
  { day: 'monday',    start: '18:00', end: '20:00' },
  { day: 'wednesday', start: '18:00', end: '20:00' },
  { day: 'friday',    start: '18:00', end: '20:00' },
  { day: 'saturday',  start: '11:00', end: '14:00' },
  { day: 'sunday',    start: '11:00', end: '13:00' },
]

const slots = ref([...DEFAULT_SLOTS])

async function fetchSlots() {
  try {
    const { data } = await api.get('/api/content/pages/regular-schedule')
    const saved = data?.data?.body?.slots
    if (Array.isArray(saved) && saved.length) slots.value = saved
  } catch {
    // page not yet saved — keep defaults
  }
}

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

onMounted(() => {
  store.fetch()
  fetchSlots()
})

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
  <section class="max-w-5xl mx-auto px-4 py-10 sm:py-16">
    <h1 class="text-center">{{ t('schedule.title') }}</h1>
    <div class="divider-engraved my-6 mx-auto w-1/3"></div>
    <p class="text-center mb-8 font-sans text-oxblood-500">{{ t('schedule.first_training_free') }}</p>

    <div class="card p-6 mb-12">
      <h2 class="mb-4 flex items-center gap-1">
        {{ t('schedule.regular') }}
        <EditableScheduleSlots :slots="slots" @updated="slots = $event" />
      </h2>
      <ul class="grid sm:grid-cols-2 md:grid-cols-3 gap-4 font-sans">
        <li v-for="slot in slots" :key="slot.day + slot.start" class="border-l-2 border-gold-500 pl-3">
          <div class="uppercase tracking-widest text-xs text-ink-500">{{ t(`schedule.${slot.day}`) }}</div>
          <div class="text-xl">{{ slot.start }} – {{ slot.end }}</div>
        </li>
      </ul>
    </div>

    <h2 class="mb-4">{{ t('schedule.upcoming') }}</h2>
    <p v-if="message" class="mb-4 text-oxblood-500 font-sans">{{ message }}</p>

    <div v-if="store.loading && !store.list.length">{{ t('common.loading') }}</div>
    <div v-else-if="!store.list.length" class="text-ink-500 italic">{{ t('schedule.no_upcoming') }}</div>
    <div v-else class="grid md:grid-cols-2 gap-4">
      <TrainingCard v-for="s in store.list" :key="s.id" :session="s"
        @register="onRegister" @unregister="onUnregister" />
    </div>
  </section>
  </div>
</template>
