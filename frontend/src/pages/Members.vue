<script setup>
import { onMounted, ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import api from '@/lib/api'
import StructuredData from '@/components/StructuredData.vue'

const { locale, t } = useI18n()
const members = ref([])
const selected = ref(null)

useHead({
  title: () => `${t('members.title')} — Blossfechten Riga`,
  meta: [
    { name: 'description', content: () => t('members.subtitle') },
    { property: 'og:title', content: () => `${t('members.title')} — Blossfechten Riga` },
  ],
})

const membersSchema = computed(() => ({
  '@context': 'https://schema.org',
  '@graph': members.value.map(m => ({
    '@type': 'Person',
    name: m.full_name,
    jobTitle: m.role_title,
    memberOf: { '@type': 'SportsClub', name: 'Blossfechten Riga' },
  })),
}))

onMounted(async () => {
  try {
    const { data } = await api.get('/api/content/members')
    members.value = data.data
  } catch {}
})

function initials(name) {
  return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
}

function bio(m) {
  if (typeof m.bio !== 'object' || Array.isArray(m.bio)) return ''
  return m.bio[locale.value] || m.bio.en || ''
}

function lineage(m) {
  return m.bio?.lineage?.[locale.value] || m.bio?.lineage?.en || ''
}

function seminars(m) {
  return m.bio?.seminars?.[locale.value] || m.bio?.seminars?.en || ''
}
</script>

<template>
  <div>
  <StructuredData v-if="members.length" :schema="membersSchema" />
  <section class="max-w-5xl mx-auto px-4 py-16">
    <h1 class="text-center">{{ t('members.title') }}</h1>
    <p class="text-center text-ink-500 italic mt-2">{{ t('members.subtitle') }}</p>
    <div class="divider-engraved my-6 mx-auto w-1/3"></div>

    <!-- Instructors -->
    <div v-if="members.some(m => m.is_instructor)" class="mb-10">
      <h2 class="text-center mb-6 text-lg uppercase tracking-widest text-gold-500 font-sans">{{ t('members.instructors') }}</h2>
      <div class="grid md:grid-cols-2 gap-6">
        <article
          v-for="m in members.filter(m => m.is_instructor)"
          :key="m.id"
          class="card p-6 flex flex-col sm:flex-row gap-5 items-start cursor-pointer hover:border-gold-500/60 transition-colors"
          @click="selected = m"
        >
          <div class="w-20 h-20 rounded-full bg-parchment-200 border-2 border-gold-500 flex items-center justify-center font-display text-2xl text-ink-900 shrink-0">
            {{ initials(m.full_name) }}
          </div>
          <div class="flex-1 min-w-0">
            <h3>{{ m.full_name }}</h3>
            <p class="text-xs uppercase tracking-[0.2em] text-gold-500 mt-1">{{ m.role_title }} · {{ m.rank }}</p>
            <p class="text-ink-500 mt-3 text-sm line-clamp-3">{{ bio(m) }}</p>
            <button class="mt-3 text-xs uppercase tracking-widest text-oxblood-500 hover:text-oxblood-700 font-sans">{{ t('members.read_more') }} →</button>
          </div>
        </article>
      </div>
    </div>

    <!-- Other members -->
    <div v-if="members.some(m => !m.is_instructor)">
      <h2 class="text-center mb-6 text-lg uppercase tracking-widest text-ink-500 font-sans">{{ t('members.other') }}</h2>
      <div class="grid md:grid-cols-2 gap-6">
        <article v-for="m in members.filter(m => !m.is_instructor)" :key="m.id" class="card p-6 flex flex-col sm:flex-row gap-5 items-start">
          <div class="w-20 h-20 rounded-full bg-parchment-200 border border-gold-500 flex items-center justify-center font-display text-2xl text-ink-900 shrink-0">
            {{ initials(m.full_name) }}
          </div>
          <div>
            <h3>{{ m.full_name }}</h3>
            <p class="text-xs uppercase tracking-[0.2em] text-gold-500 mt-1">{{ m.role_title }} · {{ m.rank }}</p>
            <p class="text-ink-500 mt-3">{{ bio(m) }}</p>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- Bio modal -->
  <Teleport to="body">
    <div
      v-if="selected"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink-900/60 backdrop-blur-sm"
      @click.self="selected = null"
    >
      <div class="card max-w-lg w-full p-8 relative max-h-[90vh] overflow-y-auto">
        <button
          class="absolute top-4 right-4 text-ink-500 hover:text-ink-900 text-2xl leading-none"
          @click="selected = null"
          :aria-label="t('common.close')"
        >×</button>
        <div class="flex items-center gap-4 mb-6">
          <div class="w-16 h-16 rounded-full bg-parchment-200 border-2 border-gold-500 flex items-center justify-center font-display text-2xl text-ink-900 shrink-0">
            {{ initials(selected.full_name) }}
          </div>
          <div>
            <h2 class="text-xl">{{ selected.full_name }}</h2>
            <p class="text-xs uppercase tracking-[0.2em] text-gold-500 mt-1">{{ selected.role_title }} · {{ selected.rank }}</p>
          </div>
        </div>
        <div class="divider-engraved mb-6"></div>
        <p class="text-ink-700 leading-relaxed">{{ bio(selected) }}</p>
        <template v-if="lineage(selected)">
          <h4 class="mt-6 mb-2 uppercase tracking-widest text-xs text-gold-500 font-sans">{{ t('members.lineage') }}</h4>
          <p class="text-ink-500 text-sm leading-relaxed">{{ lineage(selected) }}</p>
        </template>
        <template v-if="seminars(selected)">
          <h4 class="mt-6 mb-2 uppercase tracking-widest text-xs text-gold-500 font-sans">{{ t('members.seminars') }}</h4>
          <p class="text-ink-500 text-sm">{{ seminars(selected) }}</p>
        </template>
      </div>
    </div>
  </Teleport>
  </div>
</template>
