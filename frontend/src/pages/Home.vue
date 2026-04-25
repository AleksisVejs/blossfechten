<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import api from '@/lib/api'
import StructuredData from '@/components/StructuredData.vue'
import YouTubeGrid from '@/components/YouTubeGrid.vue'
import EditablePageText from '@/components/EditablePageText.vue'
import { loadCachedPage, saveCachedPage } from '@/lib/pageCache'

const { t, locale } = useI18n()
const pillars = ['tradition', 'progression', 'community']
const heroBadges = ['about.founded', 'about.meyer_badge', 'footer.guild']

const slugs = [
  'home-hero',
  'home-identity',
  'home-pillar-tradition',
  'home-pillar-progression',
  'home-pillar-community',
  'home-cta',
  'home-cta-note',
]
const pages = reactive({})
for (const slug of slugs) {
  const cached = loadCachedPage(slug)
  if (cached) pages[slug] = cached
}
const pagesLoaded = ref(slugs.every((s) => pages[s]))

function pillarSlug(p) { return `home-pillar-${p}` }

function pageTitle(slug, fallbackKey) {
  const p = pages[slug]
  return p?.title?.[locale.value] || p?.title?.en || (fallbackKey ? t(fallbackKey) : '')
}
function pageBody(slug, fallbackKey) {
  const p = pages[slug]
  return p?.body?.[locale.value] || p?.body?.en || (fallbackKey ? t(fallbackKey) : '')
}
function onPageUpdated(slug, data) {
  pages[slug] = data
  saveCachedPage(slug, data)
}

onMounted(async () => {
  await Promise.all(slugs.map(async (slug) => {
    try {
      const { data } = await api.get(`/api/content/pages/${slug}`)
      pages[slug] = data.data
      saveCachedPage(slug, data.data)
    } catch {}
  }))
  pagesLoaded.value = true
})

useHead({
  title: () => `Blossfechten Riga — ${pageTitle('home-hero', 'home.hero_title')}`,
  meta: [
    { name: 'description', content: () => pageBody('home-hero', 'home.hero_sub') },
    { property: 'og:title', content: () => `Blossfechten Riga — ${pageTitle('home-hero', 'home.hero_title')}` },
    { property: 'og:description', content: () => pageBody('home-hero', 'home.hero_sub') },
    { property: 'og:type', content: 'website' },
  ],
})

const clubSchema = {
  '@context': 'https://schema.org',
  '@type': 'SportsClub',
  name: 'Blossfechten Riga',
  description: 'Historical European Martial Arts club in Riga practising Joachim Meyer\'s 1570 longsword treatise.',
  url: 'https://blossfechten.lv',
  foundingDate: '2022',
  address: {
    '@type': 'PostalAddress',
    addressLocality: 'Riga',
    addressCountry: 'LV',
  },
  sameAs: [
    'https://www.facebook.com/BlossfechtenRiga/',
    'https://www.youtube.com/channel/UCZ5S_Xv7pzN6XiWupyhg8-A',
    'https://www.instagram.com/blossfechten_riga/',
    'https://www.tiktok.com/@blossfechten_riga',
  ],
}
</script>

<template>
  <div>
  <StructuredData :schema="clubSchema" />
  <section class="relative overflow-hidden">
    <div class="absolute inset-0 opacity-[0.14] pointer-events-none mix-blend-multiply"
      style="background-image: url('/img/meyer/four-hews.jpg'); background-size: cover; background-position: center;"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-parchment-50/40 via-parchment-50/10 to-parchment-50"></div>
    <div class="relative max-w-6xl mx-auto px-4 pt-12 sm:pt-16 md:pt-20 pb-16 sm:pb-20 md:pb-24 text-center">
      <p class="uppercase tracking-[0.3em] text-xs text-gold-500 mb-4">Anno Domini MMXXII</p>
      <h1 class="text-4xl sm:text-5xl md:text-7xl text-ink-900">
        <span>{{ pageTitle('home-hero', 'home.hero_title') }}</span>
        <EditablePageText
          v-if="pagesLoaded"
          slug="home-hero"
          field="title"
          :page="pages['home-hero']"
          @updated="onPageUpdated('home-hero', $event)"
        />
      </h1>
      <div class="divider-engraved my-6 mx-auto w-1/2"></div>
      <p class="max-w-2xl mx-auto text-lg text-ink-500 italic">
        <span>{{ pageBody('home-hero', 'home.hero_sub') }}</span>
        <EditablePageText
          v-if="pagesLoaded"
          slug="home-hero"
          field="body"
          :page="pages['home-hero']"
          @updated="onPageUpdated('home-hero', $event)"
        />
      </p>
      <div class="mt-8 flex gap-3 justify-center flex-wrap">
        <router-link :to="{ name: 'schedule' }" class="btn-primary">{{ t('home.cta_join') }}</router-link>
        <router-link :to="{ name: 'about' }" class="btn-ghost">{{ t('nav.about') }}</router-link>
        <router-link :to="{ name: 'contact' }" class="btn-ghost">{{ t('nav.contact') }}</router-link>
      </div>
      <div class="mt-8 flex flex-wrap justify-center gap-2">
        <span
          v-for="badge in heroBadges"
          :key="badge"
          class="rounded-full border border-gold-500/30 bg-parchment-100/70 px-3 py-1 text-xs tracking-wide text-ink-600"
        >
          {{ t(badge) }}
        </span>
      </div>
    </div>
  </section>

  <section class="max-w-6xl mx-auto px-4 py-10 sm:py-16">
    <div class="text-center max-w-3xl mx-auto mb-8">
      <h2 class="mb-3">
        <span>{{ pageTitle('home-identity', 'tagline') }}</span>
        <EditablePageText
          v-if="pagesLoaded"
          slug="home-identity"
          field="title"
          :page="pages['home-identity']"
          @updated="onPageUpdated('home-identity', $event)"
        />
      </h2>
      <p class="text-ink-500">
        <span>{{ pageBody('home-identity', 'home.identity') }}</span>
        <EditablePageText
          v-if="pagesLoaded"
          slug="home-identity"
          field="body"
          :page="pages['home-identity']"
          @updated="onPageUpdated('home-identity', $event)"
        />
      </p>
    </div>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-6">
      <div v-for="p in pillars" :key="p" class="card p-6 h-full">
        <h3 class="mb-2">
          <span>{{ pageTitle(pillarSlug(p), `home.pillars.${p}.t`) }}</span>
          <EditablePageText
            v-if="pagesLoaded"
            :slug="pillarSlug(p)"
            field="title"
            :page="pages[pillarSlug(p)]"
            @updated="onPageUpdated(pillarSlug(p), $event)"
          />
        </h3>
        <p class="text-ink-500">
          <span>{{ pageBody(pillarSlug(p), `home.pillars.${p}.d`) }}</span>
          <EditablePageText
            v-if="pagesLoaded"
            :slug="pillarSlug(p)"
            field="body"
            :page="pages[pillarSlug(p)]"
            @updated="onPageUpdated(pillarSlug(p), $event)"
          />
        </p>
      </div>
    </div>
  </section>

  <section class="max-w-6xl mx-auto px-4 pb-10 sm:pb-16">
    <h2 class="text-center mb-8">{{ t('gallery.videos') }}</h2>
    <YouTubeGrid />
    <div class="text-center mt-6">
      <a
        href="https://www.youtube.com/channel/UCZ5S_Xv7pzN6XiWupyhg8-A"
        target="_blank"
        rel="noopener"
        class="btn-ghost"
      >{{ t('gallery.all_videos') }} →</a>
    </div>
  </section>

  <section class="max-w-4xl mx-auto px-4 pb-8 sm:pb-12 text-center">
    <div class="divider-engraved mx-auto w-1/3 mb-6"></div>
    <h2 class="mb-3">
      <span>{{ pageTitle('home-cta', 'contact.come') }}</span>
      <EditablePageText
        v-if="pagesLoaded"
        slug="home-cta"
        field="title"
        :page="pages['home-cta']"
        @updated="onPageUpdated('home-cta', $event)"
      />
    </h2>
    <p class="text-ink-500 text-base">
      <span>{{ pageBody('home-cta', 'contact.come_body') }}</span>
      <EditablePageText
        v-if="pagesLoaded"
        slug="home-cta"
        field="body"
        :page="pages['home-cta']"
        @updated="onPageUpdated('home-cta', $event)"
      />
    </p>
    <p class="mt-3 font-sans text-oxblood-500">
      <span>{{ pageTitle('home-cta-note', 'contact.first_training_free') }}</span>
      <EditablePageText
        v-if="pagesLoaded"
        slug="home-cta-note"
        field="title"
        :page="pages['home-cta-note']"
        @updated="onPageUpdated('home-cta-note', $event)"
      />
    </p>
    <div class="mt-6">
      <router-link :to="{ name: 'schedule' }" class="btn-primary">{{ t('schedule.title') }}</router-link>
    </div>
  </section>

  </div>
</template>
