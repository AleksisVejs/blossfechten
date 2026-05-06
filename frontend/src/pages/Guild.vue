<script setup>
import { onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import EditablePageText from '@/components/EditablePageText.vue'
import EditableTextPlaceholder from '@/components/EditableTextPlaceholder.vue'
import { useEditablePages } from '@/composables/useEditablePages'

const { t, locale } = useI18n()

const slugs = [
  'guild-membership',
  'guild-intro',
  'guild-philosophy',
  'guild-code',
]
const { pages, pageLoaded, fetchPages, onPageUpdated } = useEditablePages(slugs)

onMounted(() => {
  fetchPages()
})

useHead({
  title: () => `${t('guild.title')} — Blossfechten Riga`,
  meta: [
    { name: 'description', content: () => t('guild.meta_description') },
    { property: 'og:title', content: () => `${t('guild.title')} — Blossfechten Riga` },
  ],
})
</script>

<template>
  <div>

    <!-- ① Hero: membership identity -->
    <section class="max-w-2xl mx-auto px-4 pt-12 pb-10 sm:pt-16 flex flex-col items-center text-center gap-4">
      <a href="https://www.fencersguild.com" target="_blank" rel="noopener" class="group mb-2">
        <img
          src="/img/gryf.png"
          alt="Fencer's Guild"
          class="w-36 h-36 sm:w-44 sm:h-44 object-contain opacity-70 group-hover:opacity-90 transition-opacity duration-300"
        />
      </a>

      <div class="space-y-1.5 relative">
        <p class="uppercase tracking-[0.3em] text-xs text-ink-400">
          {{ pages['guild-membership']?.title?.[locale] || pages['guild-membership']?.title?.en || (pageLoaded['guild-membership'] ? t('guild.member_of') : '') }}
          <EditablePageText
            v-if="pageLoaded['guild-membership']"
            slug="guild-membership"
            field="title"
            :page="pages['guild-membership']"
            @updated="onPageUpdated('guild-membership', $event)"
          />
        </p>
        <h1 class="font-serif text-4xl sm:text-5xl text-ink-900">
          {{ pages['guild-intro']?.title?.[locale] || pages['guild-intro']?.title?.en || t('guild.title') }}
          <EditablePageText
            v-if="pageLoaded['guild-intro']"
            slug="guild-intro"
            field="title"
            :page="pages['guild-intro']"
            @updated="onPageUpdated('guild-intro', $event)"
          />
        </h1>
        <p class="uppercase tracking-[0.25em] text-xs text-gold-600">{{ t('guild.established') }}</p>
      </div>

      <div class="divider-engraved w-1/4 my-1"></div>

      <p class="prose-parchment text-base max-w-lg leading-relaxed relative">
        {{ pages['guild-membership']?.body?.[locale] || pages['guild-membership']?.body?.en || (pageLoaded['guild-membership'] ? t('guild.member_body') : '') }}
        <EditableTextPlaceholder v-if="!pageLoaded['guild-membership']" :lines="2" width-class="w-full" />
        <EditablePageText
          v-if="pageLoaded['guild-membership']"
          slug="guild-membership"
          field="body"
          :page="pages['guild-membership']"
          @updated="onPageUpdated('guild-membership', $event)"
        />
      </p>

      <a
        href="https://www.fencersguild.com"
        target="_blank"
        rel="noopener"
        class="inline-flex items-center gap-1.5 font-sans text-xs uppercase tracking-[0.2em] text-oxblood-600 hover:text-oxblood-800 border-b border-oxblood-400/50 hover:border-oxblood-700 pb-0.5 transition-colors duration-150"
      >
        fencersguild.com
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
      </a>
    </section>

    <!-- ② History & background -->
    <section class="border-t border-parchment-300/60 bg-parchment-100/40">
      <div class="max-w-3xl mx-auto px-4 py-12">
        <div class="prose-parchment text-lg space-y-4 relative">
          <p>{{ pages['guild-intro']?.body?.[locale] || pages['guild-intro']?.body?.en || (pageLoaded['guild-intro'] ? t('guild.intro') : '') }}</p>
          <EditableTextPlaceholder v-if="!pageLoaded['guild-intro']" :lines="3" width-class="w-5/6" />
          <EditablePageText
            v-if="pageLoaded['guild-intro']"
            slug="guild-intro"
            field="body"
            :page="pages['guild-intro']"
            @updated="onPageUpdated('guild-intro', $event)"
          />
        </div>
      </div>
    </section>

    <!-- ③ Philosophy & ④ Code side by side on large screens -->
    <section class="max-w-5xl mx-auto px-4 py-14 sm:py-20 grid md:grid-cols-2 gap-10 md:gap-16">

      <div>
        <h2 class="mb-4">
          <span>{{ pages['guild-philosophy']?.title?.[locale] || pages['guild-philosophy']?.title?.en || (pageLoaded['guild-philosophy'] ? t('guild.philosophy_title') : '') }}</span>
          <EditableTextPlaceholder v-if="!pageLoaded['guild-philosophy']" width-class="w-2/3" />
          <EditablePageText
            v-if="pageLoaded['guild-philosophy']"
            slug="guild-philosophy"
            field="title"
            :page="pages['guild-philosophy']"
            @updated="onPageUpdated('guild-philosophy', $event)"
          />
        </h2>
        <div class="divider-engraved mb-6 w-1/3"></div>
        <div class="prose-parchment text-base space-y-3 relative">
          <p>{{ pages['guild-philosophy']?.body?.[locale] || pages['guild-philosophy']?.body?.en || (pageLoaded['guild-philosophy'] ? t('guild.philosophy_body') : '') }}</p>
          <EditableTextPlaceholder v-if="!pageLoaded['guild-philosophy']" :lines="4" width-class="w-5/6" />
          <EditablePageText
            v-if="pageLoaded['guild-philosophy']"
            slug="guild-philosophy"
            field="body"
            :page="pages['guild-philosophy']"
            @updated="onPageUpdated('guild-philosophy', $event)"
          />
        </div>
      </div>

      <div>
        <h2 class="mb-4">
          <span>{{ pages['guild-code']?.title?.[locale] || pages['guild-code']?.title?.en || (pageLoaded['guild-code'] ? t('guild.code_title') : '') }}</span>
          <EditableTextPlaceholder v-if="!pageLoaded['guild-code']" width-class="w-2/3" />
          <EditablePageText
            v-if="pageLoaded['guild-code']"
            slug="guild-code"
            field="title"
            :page="pages['guild-code']"
            @updated="onPageUpdated('guild-code', $event)"
          />
        </h2>
        <div class="divider-engraved mb-6 w-1/3"></div>
        <p class="italic text-gold-600 text-lg font-serif mb-4">{{ t('guild.virtues') }}</p>
        <div class="prose-parchment text-base space-y-3 relative">
          <p>{{ pages['guild-code']?.body?.[locale] || pages['guild-code']?.body?.en || (pageLoaded['guild-code'] ? t('guild.code_body') : '') }}</p>
          <EditableTextPlaceholder v-if="!pageLoaded['guild-code']" :lines="3" width-class="w-5/6" />
          <EditablePageText
            v-if="pageLoaded['guild-code']"
            slug="guild-code"
            field="body"
            :page="pages['guild-code']"
            @updated="onPageUpdated('guild-code', $event)"
          />
        </div>
      </div>

    </section>

  </div>
</template>
