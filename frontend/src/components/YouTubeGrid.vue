<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'
import { loadCachedApi, saveCachedApi } from '@/lib/pageCache'

const { t } = useI18n()
const cachedVideos = loadCachedApi('youtube-videos')
const videos = ref(Array.isArray(cachedVideos) ? cachedVideos : [])
const loading = ref(!videos.value.length)

onMounted(async () => {
  try {
    const { data } = await api.get('/api/content/videos')
    videos.value = Array.isArray(data?.data) ? data.data : []
    saveCachedApi('youtube-videos', videos.value)
  } catch {
    // silently skip if backend unavailable
  } finally {
    loading.value = false
  }
})

function formatDate(iso) {
  return new Date(iso).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}
</script>

<template>
  <div v-if="loading" class="text-center text-ink-500 italic py-8">{{ t('common.loading') }}</div>
  <div v-else-if="videos.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <a
      v-for="v in videos"
      :key="v.id"
      :href="v.url"
      target="_blank"
      rel="noopener"
      class="card group overflow-hidden flex flex-col"
    >
      <div class="relative overflow-hidden aspect-video bg-parchment-200">
        <img
          :src="v.thumbnail"
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
        <p class="text-xs text-ink-500 mt-auto font-sans">{{ formatDate(v.published) }}</p>
      </div>
    </a>
  </div>
  <p v-else class="text-center text-ink-500 italic py-8">{{ t('gallery.videos_unavailable') }}</p>
</template>
