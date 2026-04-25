<script setup>
import { onMounted, ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import api from '@/lib/api'
import StructuredData from '@/components/StructuredData.vue'
import { loadCachedApi, saveCachedApi } from '@/lib/pageCache'

const { t } = useI18n()
const members = ref(loadCachedApi('members') || [])

// Only the names are verified (from the club's prior public site).
const fallbackMembers = [
  { id: 'f1', full_name: 'Artūrs Serafims Vītiņš', is_instructor: false },
  { id: 'f2', full_name: 'Dastins Weich', is_instructor: false },
  { id: 'f3', full_name: 'Juris Matkevics', is_instructor: false },
  { id: 'f4', full_name: 'Edgars Bērsons', is_instructor: false },
]
const isFallback = ref(false)
const localPhotoByName = {
  'Artūrs Serafims Vītiņš': '/img/members/arturs-serafims-vitins.png',
  'Dastins Weich': '/img/members/dastins-weich.png',
  'Juris Matkevics': '/img/members/juris-matkevics.png',
  'Edgars Bērsons': '/img/members/edgars-bersons.png',
}

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
    memberOf: { '@type': 'SportsClub', name: 'Blossfechten Riga' },
  })),
}))

onMounted(async () => {
  try {
    const { data } = await api.get('/api/content/members')
    if (Array.isArray(data?.data) && data.data.length) {
      members.value = data.data
      saveCachedApi('members', data.data)
    } else if (!members.value.length) {
      members.value = fallbackMembers
      isFallback.value = true
    }
  } catch {
    members.value = fallbackMembers
    isFallback.value = true
  }
})

function initials(name) {
  return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
}

function resolveApiUrl(path) {
  if (!path) return ''
  if (/^https?:\/\//i.test(path)) return path
  const base = (api.defaults.baseURL || '').replace(/\/$/, '')
  if (!base) return path
  return path.startsWith('/') ? `${base}${path}` : `${base}/${path}`
}

function memberPhoto(m) {
  if (m.photo_url) return resolveApiUrl(m.photo_url)
  return localPhotoByName[m.full_name] || ''
}
</script>

<template>
  <div>
  <StructuredData v-if="members.length" :schema="membersSchema" />
  <section class="max-w-5xl mx-auto px-4 py-10 sm:py-16">
    <h1 class="text-center">{{ t('members.title') }}</h1>
    <p class="text-center text-ink-500 italic mt-2">{{ t('members.subtitle') }}</p>
    <div class="divider-engraved my-6 mx-auto w-1/3"></div>

    <p v-if="isFallback" class="text-center text-ink-500 italic mb-8 text-sm">{{ t('members.fallback_note') }}</p>

    <!-- Instructors -->
    <div v-if="members.some(m => m.is_instructor)" class="mb-10">
      <h2 class="text-center mb-6 text-lg uppercase tracking-widest text-gold-500 font-sans">{{ t('members.instructors') }}</h2>
      <div class="grid md:grid-cols-2 gap-6">
        <article
          v-for="m in members.filter(m => m.is_instructor)"
          :key="m.id"
          class="card p-6 flex flex-col sm:flex-row gap-5 items-start"
        >
          <img
            v-if="memberPhoto(m)"
            :src="memberPhoto(m)"
            :alt="m.full_name"
            class="w-20 h-20 rounded-full border-2 border-gold-500 object-cover shrink-0"
            loading="lazy"
          />
          <div v-else class="w-20 h-20 rounded-full bg-parchment-200 border-2 border-gold-500 flex items-center justify-center font-serif text-2xl text-ink-900 shrink-0">
            {{ initials(m.full_name) }}
          </div>
          <div class="flex-1 min-w-0">
            <h3>{{ m.full_name }}</h3>
          </div>
        </article>
      </div>
    </div>

    <!-- Other members -->
    <div v-if="members.some(m => !m.is_instructor)">
      <h2 class="text-center mb-6 text-lg uppercase tracking-widest text-ink-500 font-sans">{{ t('members.other') }}</h2>
      <div class="grid md:grid-cols-2 gap-6">
        <article v-for="m in members.filter(m => !m.is_instructor)" :key="m.id" class="card p-6 flex flex-col sm:flex-row gap-5 items-start">
          <img
            v-if="memberPhoto(m)"
            :src="memberPhoto(m)"
            :alt="m.full_name"
            class="w-20 h-20 rounded-full border border-gold-500 object-cover shrink-0"
            loading="lazy"
          />
          <div v-else class="w-20 h-20 rounded-full bg-parchment-200 border border-gold-500 flex items-center justify-center font-serif text-2xl text-ink-900 shrink-0">
            {{ initials(m.full_name) }}
          </div>
          <div>
            <h3>{{ m.full_name }}</h3>
          </div>
        </article>
      </div>
    </div>
  </section>
  </div>
</template>
