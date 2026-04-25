<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import api from '@/lib/api'
import EditablePageText from '@/components/EditablePageText.vue'
import { loadCachedPage, saveCachedPage } from '@/lib/pageCache'

const { t, locale } = useI18n()
const levels = ['novice', 'scholar', 'free_scholar', 'instructor', 'fechtmeister']
const curriculumSlugs = ['curriculum-i', 'curriculum-ii', 'curriculum-iii', 'curriculum-iv', 'curriculum-v']
const aboutPage = ref(loadCachedPage('about'))
const aboutPageLoaded = ref(!!aboutPage.value)
const meyerPage = ref(loadCachedPage('meyer'))
const meyerPageLoaded = ref(!!meyerPage.value)
const curriculumIntro = ref(loadCachedPage('curriculum-intro'))
const curriculumIntroLoaded = ref(!!curriculumIntro.value)
const curriculumLevels = ref(curriculumSlugs.map((s) => loadCachedPage(s)))
const curriculumLevelsLoaded = ref(curriculumLevels.value.every(Boolean))

async function loadPage(slug, target, loadedFlag) {
  try {
    const { data } = await api.get(`/api/content/pages/${slug}`)
    target.value = data.data
    saveCachedPage(slug, data.data)
  } catch {} finally {
    if (loadedFlag) loadedFlag.value = true
  }
}

async function loadCurriculumLevels() {
  await Promise.all(curriculumSlugs.map(async (slug, i) => {
    try {
      const { data } = await api.get(`/api/content/pages/${slug}`)
      curriculumLevels.value[i] = data.data
      saveCachedPage(slug, data.data)
    } catch {}
  }))
  curriculumLevelsLoaded.value = true
}

function updateCurriculumLevel(i, page) {
  const next = [...curriculumLevels.value]
  next[i] = page
  curriculumLevels.value = next
  saveCachedPage(curriculumSlugs[i], page)
}

onMounted(() => {
  loadPage('about', aboutPage, aboutPageLoaded)
  loadPage('meyer', meyerPage, meyerPageLoaded)
  loadPage('curriculum-intro', curriculumIntro, curriculumIntroLoaded)
  loadCurriculumLevels()
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
      <h1 class="text-center mt-2">
        <span>{{ aboutPage?.title?.[locale] || aboutPage?.title?.en || t('about.title') }}</span>
        <EditablePageText
          v-if="aboutPageLoaded"
          slug="about"
          field="title"
          :page="aboutPage"
          @updated="aboutPage = $event; saveCachedPage('about', $event)"
        />
      </h1>
      <div class="divider-engraved my-6 mx-auto w-1/3"></div>
      <div class="prose-parchment text-lg space-y-4 relative">
        <p>{{ aboutPage?.body?.[locale] || aboutPage?.body?.en || t('about.body_single') }}</p>
        <EditablePageText
          v-if="aboutPageLoaded"
          slug="about"
          field="body"
          :page="aboutPage"
          @updated="aboutPage = $event; saveCachedPage('about', $event)"
        />
      </div>
      <figure class="mt-10 flex flex-col items-center">
        <img src="/img/meyer/portrait.jpg" alt="Joachim Meyer"
          class="w-40 h-40 object-cover rounded-full border-4 border-gold-500/60 shadow-md sepia-[0.2]" />
        <figcaption class="mt-3 text-xs italic text-ink-500">Joachim Meyer, Freifechter (c. 1537–1571)</figcaption>
      </figure>
    </section>

    <section class="max-w-5xl mx-auto px-4 pb-12 grid lg:grid-cols-[1fr,300px] gap-8 lg:gap-10 items-start">
      <div>
        <h2>
          <span>{{ meyerPage?.title?.[locale] || meyerPage?.title?.en || t('meyer.title') }}</span>
          <EditablePageText
            v-if="meyerPageLoaded"
            slug="meyer"
            field="title"
            :page="meyerPage"
            @updated="meyerPage = $event; saveCachedPage('meyer', $event)"
          />
        </h2>
        <div class="divider-engraved my-6 w-1/3"></div>
        <div class="prose-parchment text-lg">
          <p v-if="meyerPage">{{ meyerPage.body?.[locale] || meyerPage.body?.en }}</p>
          <p v-else-if="meyerPageLoaded">{{ t('about.body_1') }}</p>
          <EditablePageText
            v-if="meyerPageLoaded"
            slug="meyer"
            field="body"
            :page="meyerPage"
            @updated="meyerPage = $event; saveCachedPage('meyer', $event)"
          />
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
      <h2 class="text-center mb-4">
        <span>{{ curriculumIntro?.title?.[locale] || curriculumIntro?.title?.en || t('meyer.curriculum_title') }}</span>
        <EditablePageText
          v-if="curriculumIntroLoaded"
          slug="curriculum-intro"
          field="title"
          :page="curriculumIntro"
          @updated="curriculumIntro = $event; saveCachedPage('curriculum-intro', $event)"
        />
      </h2>
      <p class="text-center text-ink-500 italic max-w-2xl mx-auto mb-8">
        <span>{{ curriculumIntro?.body?.[locale] || curriculumIntro?.body?.en || t('meyer.curriculum_note') }}</span>
        <EditablePageText
          v-if="curriculumIntroLoaded"
          slug="curriculum-intro"
          field="body"
          :page="curriculumIntro"
          @updated="curriculumIntro = $event; saveCachedPage('curriculum-intro', $event)"
        />
      </p>
      <ol class="space-y-4">
        <li v-for="(lv, i) in levels" :key="lv" class="card p-5 flex gap-5">
          <div class="font-serif text-4xl text-gold-500 w-12 shrink-0">{{ ['I','II','III','IV','V'][i] }}</div>
          <div class="flex-1 min-w-0">
            <h3>
              <span>{{ curriculumLevels[i]?.title?.[locale] || curriculumLevels[i]?.title?.en || t(`meyer.levels.${lv}.t`) }}</span>
              <EditablePageText
                v-if="curriculumLevelsLoaded"
                :slug="curriculumSlugs[i]"
                field="title"
                :page="curriculumLevels[i]"
                @updated="updateCurriculumLevel(i, $event)"
              />
            </h3>
            <p class="text-ink-500 mt-1">
              <span>{{ curriculumLevels[i]?.body?.[locale] || curriculumLevels[i]?.body?.en || t(`meyer.levels.${lv}.d`) }}</span>
              <EditablePageText
                v-if="curriculumLevelsLoaded"
                :slug="curriculumSlugs[i]"
                field="body"
                :page="curriculumLevels[i]"
                @updated="updateCurriculumLevel(i, $event)"
              />
            </p>
          </div>
        </li>
      </ol>
    </section>
  </div>
</template>
