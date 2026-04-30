<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const videos = [
  { id: 'ZqmlUYZ5hZs', title: 'Kas mēs esam un ko mēs daram' },
  { id: 'zviaYIlH95A', title: 'First Baltic longsword seminar 2023' },
  { id: 'fBDb0Ffrm-U', title: 'Bauskas viduslaiku svetki. Medieval festival in Bauska - non-choreographed fight demonstration.' },
  { id: 'W_fKkqvNrcQ', title: "Fencer's guild summercamp 2023" },
  { id: 'eb3cX8VE6ao', title: "Fencer's guild annual Autumn school" },
  { id: 'e5mekCOBG4I', title: 'Blossfechten Riga 2024 summer camp' },
]
</script>

<template>
  <div v-if="videos.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <a
      v-for="v in videos"
      :key="v.id"
      :href="`https://www.youtube.com/watch?v=${v.id}`"
      target="_blank"
      rel="noopener"
      class="card group overflow-hidden flex flex-col"
    >
      <div class="relative overflow-hidden aspect-video bg-parchment-200">
        <img
          :src="`https://i.ytimg.com/vi/${v.id}/hqdefault.jpg`"
          :alt="v.title"
          class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
          loading="lazy"
        />
        <div class="absolute inset-0 flex items-center justify-center">
          <div class="w-12 h-12 rounded-full bg-oxblood-600/80 flex items-center justify-center transition-transform group-hover:scale-110">
            <svg class="w-5 h-5 text-parchment-50 ml-1" fill="currentColor" viewBox="0 0 24 24">
              <path d="M8 5v14l11-7z"/>
            </svg>
          </div>
        </div>
      </div>
      <div class="p-4 flex flex-col gap-1 flex-1">
        <p class="font-sans text-sm text-ink-900 line-clamp-2 leading-snug">{{ v.title }}</p>
      </div>
    </a>
  </div>
  <p v-else class="text-center text-ink-500 italic py-8">{{ t('gallery.videos_unavailable') }}</p>
</template>
