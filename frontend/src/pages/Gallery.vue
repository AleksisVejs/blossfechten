<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import YouTubeGrid from '@/components/YouTubeGrid.vue'

const { t } = useI18n()
const activeTab = ref('videos')

useHead({
  title: () => `${t('nav.gallery')} — Blossfechten Riga`,
  meta: [
    { name: 'description', content: 'Videos and photos from Blossfechten Riga — HEMA longsword training, sparring, and events.' },
    { property: 'og:title', content: () => `${t('nav.gallery')} — Blossfechten Riga` },
  ],
})
</script>

<template>
  <section class="max-w-6xl mx-auto px-4 py-16">
    <h1 class="text-center">{{ t('nav.gallery') }}</h1>
    <div class="divider-engraved my-6 mx-auto w-1/3"></div>

    <!-- Tab switcher -->
    <div class="flex gap-2 justify-center mb-10 font-sans text-sm uppercase tracking-widest">
      <button
        :class="activeTab === 'videos' ? 'btn-primary' : 'btn-ghost'"
        @click="activeTab = 'videos'"
      >{{ t('gallery.videos') }}</button>
      <button
        :class="activeTab === 'photos' ? 'btn-primary' : 'btn-ghost'"
        @click="activeTab = 'photos'"
      >{{ t('gallery.photos') }}</button>
      <button
        :class="activeTab === 'facebook' ? 'btn-primary' : 'btn-ghost'"
        @click="activeTab = 'facebook'"
      >Facebook</button>
    </div>

    <!-- Videos tab -->
    <div v-if="activeTab === 'videos'">
      <YouTubeGrid />
      <div class="text-center mt-8">
        <a
          href="https://www.youtube.com/channel/UCZ5S_Xv7pzN6XiWupyhg8-A"
          target="_blank"
          rel="noopener"
          class="btn-ghost"
        >{{ t('gallery.all_videos') }} →</a>
      </div>
    </div>

    <!-- Photos tab -->
    <div v-if="activeTab === 'photos'">
      <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4">
        <div
          v-for="n in 6"
          :key="n"
          class="card aspect-square overflow-hidden bg-parchment-200 flex items-center justify-center"
        >
          <img
            :src="`/img/gallery/photo-${n}.jpg`"
            :alt="`Blossfechten Riga training photo ${n}`"
            class="w-full h-full object-cover"
            loading="lazy"
            @error="$event.target.parentElement.style.display = 'none'"
          />
        </div>
      </div>
      <p class="text-center text-ink-500 italic mt-6 text-sm font-sans">{{ t('gallery.photos_note') }}</p>
    </div>

    <!-- Facebook tab -->
    <div v-if="activeTab === 'facebook'" class="flex justify-center">
      <div class="card p-6 max-w-md w-full text-center">
        <p class="text-ink-500 mb-4 font-sans text-sm">{{ t('gallery.facebook_note') }}</p>
        <a
          href="https://www.facebook.com/BlossfechtenRiga/"
          target="_blank"
          rel="noopener"
          class="btn-primary"
        >{{ t('gallery.open_facebook') }}</a>
      </div>
    </div>
  </section>
</template>
