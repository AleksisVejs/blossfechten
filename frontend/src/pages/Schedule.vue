<script setup>
import { onMounted, ref, computed, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import { useTrainingsStore } from '@/stores/trainings'
import { useAuthStore } from '@/stores/auth'
import TrainingCard from '@/components/TrainingCard.vue'
import StructuredData from '@/components/StructuredData.vue'
import EditableScheduleSlots from '@/components/EditableScheduleSlots.vue'
import EditablePageText from '@/components/EditablePageText.vue'
import EditableTextPlaceholder from '@/components/EditableTextPlaceholder.vue'
import { useEditablePages } from '@/composables/useEditablePages'
import { loadCachedApi, saveCachedApi } from '@/lib/pageCache'
import api from '@/lib/api'

const { t, locale } = useI18n()
const store = useTrainingsStore()
const auth = useAuthStore()
const message = ref('')
const trainingsReady = ref(false)
const slotsReady = ref(false)

const pageSlugs = ['schedule-header', 'schedule-regular', 'schedule-upcoming']
const { pages, pageLoaded, pagesLoaded, pageTitle, pageBody, fetchPages, onPageUpdated } = useEditablePages(pageSlugs)

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

const cachedSlots = loadCachedApi('regular-schedule-slots')
const slots = ref(Array.isArray(cachedSlots) && cachedSlots.length ? cachedSlots : [])
slotsReady.value = slots.value.length > 0

function setSlots(nextSlots) {
  slots.value = nextSlots
  saveCachedApi('regular-schedule-slots', nextSlots)
}

async function fetchSlots() {
  try {
    const { data } = await api.get('/api/content/pages/regular-schedule')
    const saved = data?.data?.body?.slots
    setSlots(Array.isArray(saved) && saved.length ? saved : [...DEFAULT_SLOTS])
  } catch {
    // page not yet saved — keep defaults
    if (!slots.value.length) setSlots([...DEFAULT_SLOTS])
  } finally {
    slotsReady.value = true
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
  store.fetch().finally(() => {
    trainingsReady.value = true
  })
  fetchSlots()
  fetchPages()
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
    <h1 class="text-center">
      <span>{{ pageTitle('schedule-header', 'schedule.title') }}</span>
      <EditableTextPlaceholder v-if="!pageLoaded['schedule-header']" width-class="w-2/3" centered />
      <EditablePageText
        v-if="pagesLoaded"
        slug="schedule-header"
        field="title"
        :page="pages['schedule-header']"
        @updated="onPageUpdated('schedule-header', $event)"
      />
    </h1>
    <div class="divider-engraved my-6 mx-auto w-1/3"></div>
    <p class="text-center mb-8 font-sans text-oxblood-500">
      <span>{{ pageBody('schedule-header', 'schedule.first_training_free') }}</span>
      <EditableTextPlaceholder v-if="!pageLoaded['schedule-header']" width-class="w-1/2" centered />
      <EditablePageText
        v-if="pagesLoaded"
        slug="schedule-header"
        field="body"
        :page="pages['schedule-header']"
        @updated="onPageUpdated('schedule-header', $event)"
      />
    </p>

    <div class="card p-6 mb-12">
      <h2 class="mb-4 flex items-center gap-1">
        <span>{{ pageTitle('schedule-regular', 'schedule.regular') }}</span>
        <EditableTextPlaceholder v-if="!pageLoaded['schedule-regular']" width-class="w-32" />
        <EditablePageText
          v-if="pagesLoaded"
          slug="schedule-regular"
          field="title"
          :page="pages['schedule-regular']"
          @updated="onPageUpdated('schedule-regular', $event)"
        />
        <EditableScheduleSlots :slots="slots" @updated="setSlots($event)" />
      </h2>
      <div v-if="!slotsReady" class="text-ink-500 italic">{{ t('common.loading') }}</div>
      <ul v-else class="grid sm:grid-cols-2 md:grid-cols-3 gap-4 font-sans">
        <li v-for="slot in slots" :key="slot.day + slot.start" class="border-l-2 border-gold-500 pl-3">
          <div class="uppercase tracking-widest text-xs text-ink-500">{{ t(`schedule.${slot.day}`) }}</div>
          <div class="text-xl">{{ slot.start }} – {{ slot.end }}</div>
        </li>
      </ul>
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <h2>
        <span>{{ pageTitle('schedule-upcoming', 'schedule.upcoming') }}</span>
        <EditableTextPlaceholder v-if="!pageLoaded['schedule-upcoming']" width-class="w-40" />
        <EditablePageText
          v-if="pagesLoaded"
          slug="schedule-upcoming"
          field="title"
          :page="pages['schedule-upcoming']"
          @updated="onPageUpdated('schedule-upcoming', $event)"
        />
      </h2>
      <router-link
        v-if="auth.isAdmin"
        :to="{ name: 'admin', query: { tab: 'trainings' } }"
        class="btn-ghost"
      >
        {{ t('admin.new_training') }}
      </router-link>
    </div>
    <p v-if="message" class="mb-4 text-oxblood-500 font-sans">{{ message }}</p>

    <div v-if="!trainingsReady" class="text-ink-500 italic">{{ t('common.loading') }}</div>
    <div v-else-if="!store.list.length" class="text-ink-500 italic">
      <span>{{ pageBody('schedule-upcoming', 'schedule.no_upcoming') }}</span>
      <EditableTextPlaceholder v-if="!pageLoaded['schedule-upcoming']" :lines="2" width-class="w-4/5" />
      <EditablePageText
        v-if="pagesLoaded"
        slug="schedule-upcoming"
        field="body"
        :page="pages['schedule-upcoming']"
        @updated="onPageUpdated('schedule-upcoming', $event)"
      />
    </div>
    <div v-else class="grid md:grid-cols-2 gap-4">
      <TrainingCard v-for="s in store.list" :key="s.id" :session="s"
        @register="onRegister" @unregister="onUnregister" />
    </div>
  </section>
  </div>
</template>
