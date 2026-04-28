<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useHead } from '@unhead/vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import api from '@/lib/api'
import { resolveMediaUrl } from '@/lib/mediaUrl'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const { t, locale } = useI18n()
const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const toast = useToast()

const loading = ref(false)
const post = ref(null)
const confirmingDelete = ref(false)
const deletingNow = ref(false)

function localText(value, fallback = '') {
  if (!value || typeof value !== 'object') return fallback
  return value[locale.value] || value.en || fallback
}

const contentBlocks = computed(() => {
  const raw = localText(post.value?.body, '')
  return raw
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean)
    .map((line) => {
      const imageMatch = line.match(/^!\[(.*?)\]\((https?:\/\/[^\s)]+|\/[^\s)]+)\)$/i)
      if (imageMatch) {
        return {
          type: 'image',
          alt: imageMatch[1] || 'Forum image',
          src: resolveMediaUrl(imageMatch[2]),
        }
      }

      return {
        type: 'text',
        text: line,
      }
    })
})

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/api/forum/${route.params.slug}`)
    post.value = data.data
  } catch {
    post.value = null
  } finally {
    loading.value = false
  }
}

watch(() => route.params.slug, load)
onMounted(load)

function editCurrentPost() {
  if (!post.value?.id && !post.value?.slug) return
  router.push({
    name: 'admin',
    query: {
      tab: 'forum',
      edit: String(post.value.id || post.value.slug),
    },
  })
}

async function deleteCurrentPost() {
  if (!post.value?.id) return
  deletingNow.value = true
  try {
    await api.delete(`/api/admin/forum-posts/${post.value.id}`)
    toast.success(t('admin.deleted'))
    confirmingDelete.value = false
    router.push({ name: 'forum' })
  } catch {
    toast.error(t('admin.error_deleting'))
  } finally {
    deletingNow.value = false
  }
}

useHead({
  title: () => `${localText(post.value?.title, t('forum.title'))} — Blossfechten Riga`,
  meta: [
    { name: 'description', content: () => localText(post.value?.excerpt, t('forum.subtitle')) },
  ],
})
</script>

<template>
  <section class="max-w-4xl mx-auto px-4 py-8 sm:py-12">
    <div v-if="loading" class="card p-8 text-center italic text-ink-500 font-sans">{{ t('common.loading') }}</div>
    <div v-else-if="!post" class="card p-8 text-center italic text-ink-500 font-sans">{{ t('common.error') }}</div>
    <article v-else class="card p-6 sm:p-8">
      <img
        v-if="post.cover_image_url"
        :src="resolveMediaUrl(post.cover_image_url)"
        :alt="localText(post.title, 'Forum post cover')"
        class="w-full h-64 sm:h-80 object-cover rounded-sm border border-parchment-300/70 mb-5"
      />
      <div class="flex flex-wrap items-center gap-2 mb-3">
        <span v-if="post.is_pinned" class="text-[10px] uppercase tracking-widest text-gold-500 border border-gold-500/50 px-2 py-1 rounded-sm">
          {{ t('forum.pinned') }}
        </span>
        <span class="text-xs text-ink-500 font-sans">
          {{ new Date(post.published_at || post.created_at).toLocaleDateString() }}
        </span>
        <span class="text-xs text-ink-500 font-sans">
          · {{ post.author?.name || 'Blossfechten Riga' }}
        </span>
      </div>
      <h1 class="!text-4xl sm:!text-5xl">{{ localText(post.title, '') }}</h1>
      <div v-if="auth.isAdmin" class="mt-4 flex flex-wrap gap-2">
        <button class="btn-ghost !py-2 !px-3" @click="editCurrentPost">
          {{ t('admin.edit') }}
        </button>
        <button class="btn-ghost !py-2 !px-3 text-oxblood-500" @click="confirmingDelete = true">
          {{ t('admin.delete') }}
        </button>
      </div>
      <p class="text-lg text-ink-500 mt-4">{{ localText(post.excerpt, '') }}</p>
      <div class="divider-engraved my-6"></div>
      <div class="prose-parchment text-ink-900 leading-relaxed">
        <template v-for="(block, idx) in contentBlocks" :key="idx">
          <p v-if="block.type === 'text'">{{ block.text }}</p>
          <img
            v-else
            :src="block.src"
            :alt="block.alt"
            class="w-full max-h-[30rem] object-contain rounded-sm border border-parchment-300/70 my-4"
            loading="lazy"
          />
        </template>
      </div>
      <div v-if="post.tags?.length" class="flex flex-wrap gap-1.5 mt-6">
        <span v-for="tag in post.tags" :key="tag" class="text-[10px] uppercase tracking-widest px-2 py-1 rounded-sm bg-parchment-200 text-ink-500">
          #{{ tag }}
        </span>
      </div>
    </article>

    <ConfirmDialog
      :open="confirmingDelete"
      destructive
      :title="t('admin.delete')"
      :message="t('admin.confirm_delete_forum_post')"
      :confirm-label="t('admin.delete')"
      :loading="deletingNow"
      @confirm="deleteCurrentPost"
      @cancel="confirmingDelete = false"
    />
  </section>
</template>
