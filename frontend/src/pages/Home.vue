<script setup>
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import StructuredData from '@/components/StructuredData.vue'
import YouTubeGrid from '@/components/YouTubeGrid.vue'
const { t } = useI18n()
const pillars = ['tradition', 'progression', 'community']
const heroBadges = ['about.founded', 'about.meyer_badge', 'footer.guild']

useHead({
  title: () => `Blossfechten Riga — ${t('home.hero_title')}`,
  meta: [
    { name: 'description', content: () => t('home.hero_sub') },
    { property: 'og:title', content: () => `Blossfechten Riga — ${t('home.hero_title')}` },
    { property: 'og:description', content: () => t('home.hero_sub') },
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
      <h1 class="text-4xl sm:text-5xl md:text-7xl text-ink-900">{{ t('home.hero_title') }}</h1>
      <div class="divider-engraved my-6 mx-auto w-1/2"></div>
      <p class="max-w-2xl mx-auto text-lg text-ink-500 italic">{{ t('home.hero_sub') }}</p>
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
      <h2 class="mb-3">{{ t('tagline') }}</h2>
      <p class="text-ink-500">{{ t('home.identity') }}</p>
    </div>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-6">
      <div v-for="p in pillars" :key="p" class="card p-6 h-full">
        <h3 class="mb-2">{{ t(`home.pillars.${p}.t`) }}</h3>
        <p class="text-ink-500">{{ t(`home.pillars.${p}.d`) }}</p>
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
    <h2 class="mb-3">{{ t('contact.come') }}</h2>
    <p class="text-ink-500 text-base">{{ t('contact.come_body') }}</p>
    <p class="mt-3 font-sans text-oxblood-500">{{ t('contact.first_training_free') }}</p>
    <div class="mt-6">
      <router-link :to="{ name: 'schedule' }" class="btn-primary">{{ t('schedule.title') }}</router-link>
    </div>
  </section>

  </div>
</template>
