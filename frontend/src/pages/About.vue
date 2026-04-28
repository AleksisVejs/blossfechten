<script setup>
import { onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import EditablePageText from '@/components/EditablePageText.vue'
import EditableTextPlaceholder from '@/components/EditableTextPlaceholder.vue'
import { useEditablePages } from '@/composables/useEditablePages'

const { t, locale } = useI18n()
const levels = ['novice', 'scholar', 'free_scholar', 'instructor', 'fechtmeister']
const curriculumSlugs = ['curriculum-i', 'curriculum-ii', 'curriculum-iii', 'curriculum-iv', 'curriculum-v']
const slugs = ['about', 'meyer', 'curriculum-intro', ...curriculumSlugs]
const { pages, pageLoaded, fetchPages, onPageUpdated } = useEditablePages(slugs)

onMounted(() => {
  fetchPages()
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
        <span>{{ pages.about?.title?.[locale] || pages.about?.title?.en || (pageLoaded.about ? t('about.title') : '') }}</span>
        <EditableTextPlaceholder v-if="!pageLoaded.about" width-class="w-2/3" centered />
        <EditablePageText
          v-if="pageLoaded.about"
          slug="about"
          field="title"
          :page="pages.about"
          @updated="onPageUpdated('about', $event)"
        />
      </h1>
      <div class="divider-engraved my-6 mx-auto w-1/3"></div>
      <div class="prose-parchment text-lg space-y-4 relative">
        <p>{{ pages.about?.body?.[locale] || pages.about?.body?.en || (pageLoaded.about ? t('about.body_single') : '') }}</p>
        <EditableTextPlaceholder v-if="!pageLoaded.about" :lines="3" width-class="w-5/6" />
        <EditablePageText
          v-if="pageLoaded.about"
          slug="about"
          field="body"
          :page="pages.about"
          @updated="onPageUpdated('about', $event)"
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
          <span>{{ pages.meyer?.title?.[locale] || pages.meyer?.title?.en || (pageLoaded.meyer ? t('meyer.title') : '') }}</span>
          <EditableTextPlaceholder v-if="!pageLoaded.meyer" width-class="w-2/3" />
          <EditablePageText
            v-if="pageLoaded.meyer"
            slug="meyer"
            field="title"
            :page="pages.meyer"
            @updated="onPageUpdated('meyer', $event)"
          />
        </h2>
        <div class="divider-engraved my-6 w-1/3"></div>
        <div class="prose-parchment text-lg">
          <p v-if="pages.meyer">{{ pages.meyer.body?.[locale] || pages.meyer.body?.en }}</p>
          <p v-else-if="pageLoaded.meyer">{{ t('about.body_1') }}</p>
          <EditableTextPlaceholder v-else :lines="3" width-class="w-5/6" />
          <EditablePageText
            v-if="pageLoaded.meyer"
            slug="meyer"
            field="body"
            :page="pages.meyer"
            @updated="onPageUpdated('meyer', $event)"
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
        <span>{{ pages['curriculum-intro']?.title?.[locale] || pages['curriculum-intro']?.title?.en || (pageLoaded['curriculum-intro'] ? t('meyer.curriculum_title') : '') }}</span>
        <EditableTextPlaceholder v-if="!pageLoaded['curriculum-intro']" width-class="w-2/3" centered />
        <EditablePageText
          v-if="pageLoaded['curriculum-intro']"
          slug="curriculum-intro"
          field="title"
          :page="pages['curriculum-intro']"
          @updated="onPageUpdated('curriculum-intro', $event)"
        />
      </h2>
      <p class="text-center text-ink-500 italic max-w-2xl mx-auto mb-8">
        <span>{{ pages['curriculum-intro']?.body?.[locale] || pages['curriculum-intro']?.body?.en || (pageLoaded['curriculum-intro'] ? t('meyer.curriculum_note') : '') }}</span>
        <EditableTextPlaceholder v-if="!pageLoaded['curriculum-intro']" :lines="2" width-class="w-5/6" centered />
        <EditablePageText
          v-if="pageLoaded['curriculum-intro']"
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
              <span>{{ pages[curriculumSlugs[i]]?.title?.[locale] || pages[curriculumSlugs[i]]?.title?.en || (pageLoaded[curriculumSlugs[i]] ? t(`meyer.levels.${lv}.t`) : '') }}</span>
              <EditableTextPlaceholder v-if="!pageLoaded[curriculumSlugs[i]]" width-class="w-1/2" />
              <EditablePageText
                v-if="pageLoaded[curriculumSlugs[i]]"
                :slug="curriculumSlugs[i]"
                field="title"
                :page="pages[curriculumSlugs[i]]"
                @updated="onPageUpdated(curriculumSlugs[i], $event)"
              />
            </h3>
            <p class="text-ink-500 mt-1">
              <span>{{ pages[curriculumSlugs[i]]?.body?.[locale] || pages[curriculumSlugs[i]]?.body?.en || (pageLoaded[curriculumSlugs[i]] ? t(`meyer.levels.${lv}.d`) : '') }}</span>
              <EditableTextPlaceholder v-if="!pageLoaded[curriculumSlugs[i]]" :lines="2" width-class="w-4/5" />
              <EditablePageText
                v-if="pageLoaded[curriculumSlugs[i]]"
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
