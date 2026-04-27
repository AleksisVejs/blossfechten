<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import api from '@/lib/api'
import EditablePageText from '@/components/EditablePageText.vue'
import { loadCachedPage, saveCachedPage } from '@/lib/pageCache'

const { locale, t } = useI18n()
const levels = ['novice', 'scholar', 'free_scholar', 'instructor', 'fechtmeister']
const curriculumSlugs = ['curriculum-i', 'curriculum-ii', 'curriculum-iii', 'curriculum-iv', 'curriculum-v']

const slugs = ['meyer', 'curriculum-intro', ...curriculumSlugs]
const pages = reactive({})
for (const slug of slugs) {
  const cached = loadCachedPage(slug)
  if (cached) pages[slug] = cached
}
const pagesLoaded = ref(slugs.every((s) => pages[s]))

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
  title: () => `${t('meyer.title')} — Blossfechten Riga`,
  meta: [
    { name: 'description', content: () => `${t('meyer.curriculum_title')}: ${levels.map(l => t(`meyer.levels.${l}.t`)).join(', ')}.` },
    { property: 'og:title', content: () => `${t('meyer.title')} — Blossfechten Riga` },
  ],
})
</script>

<template>
  <div>
  <section class="max-w-5xl mx-auto px-4 py-10 sm:py-16 grid lg:grid-cols-[1fr,300px] gap-8 lg:gap-10 items-start">
    <div>
      <h1>
        <span>{{ pageTitle('meyer', 'meyer.title') }}</span>
        <EditablePageText
          v-if="pagesLoaded"
          slug="meyer"
          field="title"
          :page="pages['meyer']"
          @updated="onPageUpdated('meyer', $event)"
        />
      </h1>
      <div class="divider-engraved my-6 w-1/3"></div>
      <div class="prose-parchment text-lg">
        <p>
          <span>{{ pageBody('meyer', '') }}</span>
          <EditablePageText
            v-if="pagesLoaded"
            slug="meyer"
            field="body"
            :page="pages['meyer']"
            @updated="onPageUpdated('meyer', $event)"
          />
        </p>
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
      <span>{{ pageTitle('curriculum-intro', 'meyer.curriculum_title') }}</span>
      <EditablePageText
        v-if="pagesLoaded"
        slug="curriculum-intro"
        field="title"
        :page="pages['curriculum-intro']"
        @updated="onPageUpdated('curriculum-intro', $event)"
      />
    </h2>
    <p class="text-center text-ink-500 italic max-w-2xl mx-auto mb-8">
      <span>{{ pageBody('curriculum-intro', 'meyer.curriculum_note') }}</span>
      <EditablePageText
        v-if="pagesLoaded"
        slug="curriculum-intro"
        field="body"
        :page="pages['curriculum-intro']"
        @updated="onPageUpdated('curriculum-intro', $event)"
      />
    </p>
    <ol class="space-y-4">
      <li v-for="(lv, i) in levels" :key="lv" class="card p-5 flex gap-5">
        <div class="font-serif text-4xl text-gold-500 w-12 shrink-0">{{ ['I','II','III','IV','V'][i] }}</div>
        <div class="flex-1 min-w-0">
          <h3>
            <span>{{ pageTitle(curriculumSlugs[i], `meyer.levels.${lv}.t`) }}</span>
            <EditablePageText
              v-if="pagesLoaded"
              :slug="curriculumSlugs[i]"
              field="title"
              :page="pages[curriculumSlugs[i]]"
              @updated="onPageUpdated(curriculumSlugs[i], $event)"
            />
          </h3>
          <p class="text-ink-500 mt-1">
            <span>{{ pageBody(curriculumSlugs[i], `meyer.levels.${lv}.d`) }}</span>
            <EditablePageText
              v-if="pagesLoaded"
              :slug="curriculumSlugs[i]"
              field="body"
              :page="pages[curriculumSlugs[i]]"
              @updated="onPageUpdated(curriculumSlugs[i], $event)"
            />
          </p>
        </div>
      </li>
    </ol>
  </section>
  </div>
</template>
