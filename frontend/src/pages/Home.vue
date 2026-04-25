<script setup>
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import StructuredData from '@/components/StructuredData.vue'
import YouTubeGrid from '@/components/YouTubeGrid.vue'
const { t } = useI18n()
const pillars = ['tradition', 'progression', 'community']

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
      <h1 class="font-display text-4xl sm:text-5xl md:text-7xl text-ink-900">{{ t('home.hero_title') }}</h1>
      <div class="divider-engraved my-6 mx-auto w-1/2"></div>
      <p class="max-w-2xl mx-auto text-lg text-ink-500 italic">{{ t('home.hero_sub') }}</p>
      <div class="mt-8 flex gap-3 justify-center flex-wrap">
        <router-link :to="{ name: 'schedule' }" class="btn-primary">{{ t('home.cta_join') }}</router-link>
        <router-link :to="{ name: 'meyer' }" class="btn-ghost">{{ t('home.cta_learn') }}</router-link>
      </div>
    </div>
  </section>

  <section class="max-w-6xl mx-auto px-4 py-16 grid sm:grid-cols-2 md:grid-cols-3 gap-6">
    <div v-for="p in pillars" :key="p" class="card p-6">
      <h3 class="mb-2">{{ t(`home.pillars.${p}.t`) }}</h3>
      <p class="text-ink-500">{{ t(`home.pillars.${p}.d`) }}</p>
    </div>
  </section>

  <section class="max-w-6xl mx-auto px-4 pb-16">
    <h2 class="text-center mb-8">{{ t('gallery.videos') }}</h2>
    <YouTubeGrid />
    <div class="text-center mt-6">
      <router-link :to="{ name: 'gallery' }" class="btn-ghost">{{ t('gallery.all_videos') }} →</router-link>
    </div>
  </section>

  <section class="max-w-3xl mx-auto px-4 pb-12 text-center">
    <div class="divider-engraved mx-auto w-1/3 mb-6"></div>
    <p class="text-ink-500 italic text-base">{{ t('home.identity') }}</p>
  </section>

  <section class="max-w-6xl mx-auto px-4 pb-20 text-center">
    <div class="divider-engraved mx-auto w-1/3 mb-6"></div>
    <blockquote class="font-display text-2xl md:text-3xl italic text-ink-900">
      „Das Fechten ist eine ritterliche Kunst.“
    </blockquote>
    <p class="uppercase tracking-widest text-xs text-ink-500 mt-3">— Joachim Meyer, 1570</p>
  </section>
  </div>
</template>
