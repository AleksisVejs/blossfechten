<script setup>
import { computed, ref, watch } from 'vue'
import { useHead } from '@unhead/vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import api from '@/lib/api'
import { parseLocalDateTime } from '@/lib/datetime'
import { resolveMediaUrl } from '@/lib/mediaUrl'
import EditablePageText from '@/components/EditablePageText.vue'
import { useEditablePages } from '@/composables/useEditablePages'
import { useAuthStore } from '@/stores/auth'

const { t, locale } = useI18n()
const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const { pages, pageLoaded, fetchPages, onPageUpdated } = useEditablePages(['forum-header'])

const loading = ref(false)
const posts = ref([])
const tags = ref([])
const page = ref(1)
const totalPages = ref(1)
const search = ref('')
const activeTag = ref('')

function localText(value, fallback = '') {
  if (!value || typeof value !== 'object') return fallback
  return value[locale.value] || value.en || fallback
}

const filteredTags = computed(() => tags.value.filter(Boolean))
const sectionTitle = computed(() => pages['forum-header']?.title?.[locale.value] || pages['forum-header']?.title?.en || (pageLoaded['forum-header'] ? t('forum.title') : ''))
const sectionBody = computed(() => pages['forum-header']?.body?.[locale.value] || pages['forum-header']?.body?.en || (pageLoaded['forum-header'] ? t('forum.subtitle') : ''))

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/api/forum', {
      params: {
        q: search.value || undefined,
        tag: activeTag.value || undefined,
        page: page.value,
      },
    })

    const payload = data.data
    posts.value = payload.data || []
    page.value = payload.current_page || 1
    totalPages.value = payload.last_page || 1

    tags.value = [...new Set(
      (payload.data || [])
        .flatMap((item) => item.tags || [])
        .map((item) => item?.toString().trim().toLowerCase())
        .filter(Boolean),
    )]
  } finally {
    loading.value = false
  }
}

function applyFilters() {
  page.value = 1
  router.replace({
    query: {
      ...route.query,
      q: search.value || undefined,
      tag: activeTag.value || undefined,
      page: 1,
    },
  })
}

function openPage(nextPage) {
  if (nextPage < 1 || nextPage > totalPages.value) return
  router.replace({
    query: {
      ...route.query,
      page: nextPage,
    },
  })
}

watch(
  () => route.query,
  async (query) => {
    fetchPages()
    search.value = typeof query.q === 'string' ? query.q : ''
    activeTag.value = typeof query.tag === 'string' ? query.tag : ''
    page.value = Number(query.page) > 0 ? Number(query.page) : 1
    await load()
  },
  { immediate: true },
)

useHead({
  title: () => `${t('forum.title')} — Blossfechten Riga`,
  meta: [
    { name: 'description', content: () => t('forum.subtitle') },
  ],
})
</script>

<template>
  <section class="max-w-6xl mx-auto px-4 py-8 sm:py-12">
    <div class="flex items-end justify-between gap-4 flex-wrap">
      <div>
        <h1>
          {{ sectionTitle }}
          <EditablePageText
            v-if="pageLoaded['forum-header']"
            slug="forum-header"
            field="title"
            :page="pages['forum-header']"
            @updated="onPageUpdated('forum-header', $event)"
          />
        </h1>
        <p class="mt-2 text-ink-500">
          {{ sectionBody }}
          <EditablePageText
            v-if="pageLoaded['forum-header']"
            slug="forum-header"
            field="body"
            :page="pages['forum-header']"
            @updated="onPageUpdated('forum-header', $event)"
          />
        </p>
      </div>
      <div v-if="auth.isAdmin">
        <button class="btn-primary !px-3 !py-2" @click="router.push({ name: 'admin', query: { tab: 'forum', new: '1' } })" :title="t('admin.new_forum_post')">
          +
        </button>
      </div>
    </div>
    <div class="divider-engraved my-6 w-1/3"></div>

    <div class="card p-4 mb-6">
      <div class="flex flex-wrap gap-2 items-center">
        <input
          v-model="search"
          type="search"
          :placeholder="t('forum.search_placeholder')"
          class="field-input !w-auto min-w-[220px] !py-2"
          @keyup.enter="applyFilters"
        />
        <button class="btn-primary !py-2" @click="applyFilters">{{ t('forum.search') }}</button>
        <button class="btn-ghost !py-2" @click="() => { search = ''; activeTag = ''; applyFilters() }">
          {{ t('forum.clear_filters') }}
        </button>
      </div>
      <div v-if="filteredTags.length" class="flex flex-wrap gap-2 mt-4">
        <button
          v-for="tag in filteredTags"
          :key="tag"
          class="px-2.5 py-1 text-xs uppercase tracking-widest border rounded-sm transition-colors"
          :class="activeTag === tag ? 'border-gold-500 text-gold-500 bg-gold-500/5' : 'border-parchment-300 text-ink-500 hover:border-ink-500/40'"
          @click="() => { activeTag = activeTag === tag ? '' : tag; applyFilters() }"
        >
          #{{ tag }}
        </button>
      </div>
    </div>

    <div v-if="loading" class="card p-8 text-center italic text-ink-500 font-sans">{{ t('common.loading') }}</div>
    <div v-else-if="!posts.length" class="card p-8 text-center italic text-ink-500 font-sans">{{ t('forum.empty') }}</div>
    <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
      <article v-for="post in posts" :key="post.id" class="card p-5 flex flex-col gap-3">
        <img
          v-if="post.cover_image_url"
          :src="resolveMediaUrl(post.cover_image_url)"
          :alt="localText(post.title, 'Forum post cover')"
          class="w-full h-44 object-cover rounded-sm border border-parchment-300/70"
        />
        <div class="flex items-center gap-2">
          <span v-if="post.is_pinned" class="text-[10px] uppercase tracking-widest text-gold-500 border border-gold-500/50 px-2 py-1 rounded-sm">
            {{ t('forum.pinned') }}
          </span>
          <span class="text-xs text-ink-500 font-sans">
            {{ parseLocalDateTime(post.published_at || post.created_at).toLocaleDateString() }}
          </span>
        </div>
        <h3 class="!text-2xl">{{ localText(post.title, 'Forum post') }}</h3>
        <p class="text-ink-500 leading-relaxed">{{ localText(post.excerpt, '') }}</p>
        <div v-if="post.tags?.length" class="flex flex-wrap gap-1.5 mt-auto">
          <span v-for="tag in post.tags" :key="tag" class="text-[10px] uppercase tracking-widest px-2 py-1 rounded-sm bg-parchment-200 text-ink-500">
            #{{ tag }}
          </span>
        </div>
        <router-link :to="{ name: 'forum-post', params: { slug: post.slug } }" class="btn-primary mt-1 !py-2.5 justify-center">
          {{ t('forum.read_more') }}
        </router-link>
      </article>
    </div>

    <div v-if="totalPages > 1" class="flex items-center justify-center gap-2 mt-8 font-sans text-sm">
      <button class="btn-ghost !py-1.5 !px-3" :disabled="page === 1" @click="openPage(page - 1)">{{ t('forum.previous') }}</button>
      <span>{{ page }} / {{ totalPages }}</span>
      <button class="btn-ghost !py-1.5 !px-3" :disabled="page === totalPages" @click="openPage(page + 1)">{{ t('forum.next') }}</button>
    </div>
  </section>
</template>
