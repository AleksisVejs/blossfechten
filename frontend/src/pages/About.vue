<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import api from '@/lib/api'

const { t, locale } = useI18n()
const levels = ['novice', 'scholar', 'free_scholar', 'instructor', 'fechtmeister']
const meyerPage = ref(null)
const meyerPageLoaded = ref(false)

onMounted(async () => {
  try {
    const { data } = await api.get('/api/content/pages/meyer')
    meyerPage.value = data.data
  } catch {} finally {
    meyerPageLoaded.value = true
  }
})

useHead({
  title: () => `${t('about.title')} — Blossfechten Riga`,
  meta: [
    { name: 'description', content: () => `${t('about.founded')}. ${t('meyer.curriculum_title')}: ${levels.map(l => t(`meyer.levels.${l}.t`)).join(', ')}.` },
    { property: 'og:title', content: () => `${t('about.title')} — Blossfechten Riga` },
  ],
})
</script>

<template>
  <div>
    <section class="max-w-3xl mx-auto px-4 py-10 sm:py-16">
      <p class="uppercase tracking-[0.3em] text-xs text-gold-500 text-center">{{ t('about.founded') }}</p>
      <h1 class="text-center mt-2">{{ t('about.title') }}</h1>
      <div class="divider-engraved my-6 mx-auto w-1/3"></div>
      <div class="prose-parchment text-lg space-y-4">
        <p>{{ t('about.body_single') }}</p>
      </div>
      <figure class="mt-10 flex flex-col items-center">
        <img src="/img/meyer/portrait.jpg" alt="Joachim Meyer"
          class="w-40 h-40 object-cover rounded-full border-4 border-gold-500/60 shadow-md sepia-[0.2]" />
        <figcaption class="mt-3 text-xs italic text-ink-500">Joachim Meyer, Freifechter (c. 1537–1571)</figcaption>
      </figure>
    </section>

    <section class="max-w-5xl mx-auto px-4 pb-12 grid lg:grid-cols-[1fr,300px] gap-8 lg:gap-10 items-start">
      <div>
        <h2>{{ t('meyer.title') }}</h2>
        <div class="divider-engraved my-6 w-1/3"></div>
        <div class="prose-parchment text-lg">
          <p v-if="meyerPage">{{ meyerPage.body?.[locale] || meyerPage.body?.en }}</p>
          <p v-else-if="meyerPageLoaded">{{ t('about.body_1') }}</p>
        </div>
      </div>
      <figure class="shrink-0">
        <img src="/img/meyer/cover.jpg" alt="Title page of Meyer's 1570 treatise"
          class="w-full border-4 border-parchment-300 shadow-md sepia-[0.15]" />
        <figcaption class="mt-2 text-xs text-ink-500 italic text-center">
          Gründtliche Beschreibung… Strasbourg, 1570
        </figcaption>
      </figure>
    </section>

    <section class="max-w-5xl mx-auto px-4 pb-12">
      <figure class="card p-4">
        <img src="/img/meyer/four-hews.jpg" alt="Meyer — Of Four Hews (Von vier Häwen)"
          class="w-full rounded-sm" />
        <figcaption class="mt-3 text-sm text-ink-500 italic text-center">
          « Von vier Häwen » — the four cutting lines, from Meyer's dussack section. Woodcut by Tobias Stimmer, 1570.
        </figcaption>
      </figure>
    </section>

    <section class="max-w-5xl mx-auto px-4 pb-12 sm:pb-20">
      <h2 class="text-center mb-4">{{ t('meyer.curriculum_title') }}</h2>
      <p class="text-center text-ink-500 italic max-w-2xl mx-auto mb-8">{{ t('meyer.curriculum_note') }}</p>
      <ol class="space-y-4">
        <li v-for="(lv, i) in levels" :key="lv" class="card p-5 flex gap-5">
          <div class="font-serif text-4xl text-gold-500 w-12 shrink-0">{{ ['I','II','III','IV','V'][i] }}</div>
          <div>
            <h3>{{ t(`meyer.levels.${lv}.t`) }}</h3>
            <p class="text-ink-500 mt-1">{{ t(`meyer.levels.${lv}.d`) }}</p>
          </div>
        </li>
      </ol>
    </section>
  </div>
</template>
