<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import api from '@/lib/api'
import { resolveMediaUrl } from '@/lib/mediaUrl'
import { useToast } from '@/composables/useToast'
import ConfirmDialog from '@/components/ConfirmDialog.vue'

const { t } = useI18n()
const toast = useToast()
const route = useRoute()
const router = useRouter()

const langs = ['lv', 'en', 'ru', 'cs', 'de']
const posts = ref([])
const loading = ref(false)
const saving = ref(false)
const uploadingCover = ref(false)
const uploadingInlineByLang = ref({})
const coverInput = ref(null)
const bodyRefs = ref({})
const activeBodyLang = ref('en')
const pendingUploads = ref(new Set())
const editing = ref(null)
const search = ref('')
const status = ref('all')
const confirmingDelete = ref(null)
const deletingNow = ref(false)

const form = ref(emptyForm())

function emptyForm() {
  return {
    id: null,
    title: { lv: '', en: '', ru: '', cs: '', de: '' },
    excerpt: { lv: '', en: '', ru: '', cs: '', de: '' },
    body: { lv: '', en: '', ru: '', cs: '', de: '' },
    slug: '',
    slug_locked: false,
    cover_image_url: '',
    tags: '',
    is_pinned: false,
    published: true,
    published_at: '',
  }
}

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  return posts.value.filter((item) => {
    if (status.value === 'published' && !item.published) return false
    if (status.value === 'draft' && item.published) return false
    if (!q) return true

    const hay = [
      item.slug,
      ...Object.values(item.title || {}),
      ...(item.tags || []),
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()

    return hay.includes(q)
  })
})

function localTitle(item) {
  return item?.title?.en || item?.title?.lv || 'Untitled'
}

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/api/admin/forum-posts')
    posts.value = data.data.data || []
  } catch {
    toast.error(t('admin.error_loading'))
  } finally {
    loading.value = false
  }
}

onMounted(load)

watch(
  () => route.query.new,
  (newFlag) => {
    if (newFlag === '1') {
      newOne()
      router.replace({ query: { ...route.query, new: undefined } })
    }
  },
  { immediate: true },
)

watch(
  () => [route.query.edit, posts.value.length],
  ([editValue]) => {
    if (typeof editValue !== 'string' || !editValue.trim()) return
    const match = posts.value.find((item) => String(item.id) === editValue || item.slug === editValue)
    if (!match) return
    edit(match)
    router.replace({ query: { ...route.query, edit: undefined } })
  },
  { immediate: true },
)

function edit(row) {
  editing.value = row.id
  pendingUploads.value = new Set()
  form.value = {
    ...emptyForm(),
    ...row,
    title: { ...emptyForm().title, ...(row.title || {}) },
    excerpt: { ...emptyForm().excerpt, ...(row.excerpt || {}) },
    body: { ...emptyForm().body, ...(row.body || {}) },
    tags: (row.tags || []).join(', '),
    slug_locked: true,
    published_at: toDateTimeLocal(row.published_at),
  }
}

function newOne() {
  editing.value = 'new'
  form.value = emptyForm()
  pendingUploads.value = new Set()
}

async function cancel() {
  await cleanupPendingUploads()
  editing.value = null
}

async function save(andCopyUrl = false) {
  const hasTitle = !!(form.value.title.en?.trim() || form.value.title.lv?.trim())
  const bodyLength = (form.value.body.en || form.value.body.lv || '').trim().length

  if (!hasTitle) {
    toast.error(t('admin.forum_title_required'))
    return
  }
  if (bodyLength < 20) {
    toast.error(t('admin.forum_body_too_short'))
    return
  }

  saving.value = true
  try {
    const payload = {
      ...form.value,
      tags: form.value.tags
        .split(',')
        .map((part) => part.trim().toLowerCase())
        .filter(Boolean),
      published_at: form.value.published ? (form.value.published_at || null) : null,
    }

    const response = editing.value === 'new'
      ? await api.post('/api/admin/forum-posts', payload)
      : await api.put(`/api/admin/forum-posts/${editing.value}`, payload)

    const savedSlug = response?.data?.data?.slug || form.value.slug
    if (andCopyUrl && savedSlug) {
      await navigator.clipboard.writeText(`${window.location.origin}/forum/${savedSlug}`)
      toast.success(t('admin.forum_url_copied'))
    } else {
      toast.success(t('admin.saved'))
    }

    pendingUploads.value = new Set()
    editing.value = null
    await load()
  } catch {
    toast.error(t('admin.error_saving'))
  } finally {
    saving.value = false
  }
}

async function onCoverFileChange(event) {
  const file = event.target.files?.[0]
  if (!file) return

  uploadingCover.value = true
  try {
    const payload = new FormData()
    payload.append('cover', file)
    const { data } = await api.post('/api/admin/forum-posts/upload-cover', payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    const previous = form.value.cover_image_url
    form.value.cover_image_url = data?.data?.cover_image_url || form.value.cover_image_url
    if (form.value.cover_image_url) {
      pendingUploads.value.add(form.value.cover_image_url)
    }
    if (previous && pendingUploads.value.has(previous)) {
      pendingUploads.value.delete(previous)
      await deleteUploadedImage(previous, false)
    }
    toast.success(t('admin.saved'))
  } catch {
    toast.error(t('admin.error_saving'))
  } finally {
    uploadingCover.value = false
    event.target.value = ''
  }
}

function insertAtCursor(textareaEl, valueToInsert, fallbackLang) {
  const current = (form.value.body?.[fallbackLang] || '').toString()
  const start = textareaEl?.selectionStart ?? current.length
  const end = textareaEl?.selectionEnd ?? current.length
  const next = `${current.slice(0, start)}${valueToInsert}${current.slice(end)}`
  form.value.body[fallbackLang] = next

  requestAnimationFrame(() => {
    if (!textareaEl) return
    const pos = start + valueToInsert.length
    textareaEl.focus()
    textareaEl.setSelectionRange(pos, pos)
  })
}

function setBodyRef(lang, element) {
  if (!element) return
  bodyRefs.value[lang] = element
}

function withSelection(lang, prefix, suffix = '') {
  const textareaEl = bodyRefs.value[lang]
  if (!textareaEl) return

  const start = textareaEl.selectionStart ?? 0
  const end = textareaEl.selectionEnd ?? 0
  const current = form.value.body[lang] || ''
  const selected = current.slice(start, end)
  const replacement = `${prefix}${selected}${suffix}`
  form.value.body[lang] = `${current.slice(0, start)}${replacement}${current.slice(end)}`

  requestAnimationFrame(() => {
    textareaEl.focus()
    const cursor = start + replacement.length
    textareaEl.setSelectionRange(cursor, cursor)
  })
}

async function uploadInlineImage(file, lang, textareaEl) {
  if (!file) return
  if (!file.type?.startsWith('image/')) {
    toast.error(t('admin.inline_image_invalid_type'))
    return
  }

  uploadingInlineByLang.value = { ...uploadingInlineByLang.value, [lang]: true }
  try {
    const payload = new FormData()
    payload.append('image', file)
    payload.append('file', file)
    const { data } = await api.post('/api/admin/forum-posts/upload-inline-image', payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    const imageUrl = data?.data?.image_url
    if (!imageUrl) {
      throw new Error('missing-image-url')
    }

    pendingUploads.value.add(imageUrl)
    const markdownLine = `\n![Forum image](${imageUrl})\n`
    insertAtCursor(textareaEl, markdownLine, lang)
    toast.success(t('admin.saved'))
  } catch (e) {
    toast.error(e.response?.data?.message || t('admin.error_saving'))
  } finally {
    uploadingInlineByLang.value = { ...uploadingInlineByLang.value, [lang]: false }
  }
}

async function onBodyDrop(event, lang) {
  event.preventDefault()
  const file = event.dataTransfer?.files?.[0]
  if (!file || !file.type.startsWith('image/')) return
  await uploadInlineImage(file, lang, event.target)
}

function onBodyDragOver(event) {
  event.preventDefault()
}

async function onBodyFilePick(event, lang) {
  const file = event.target.files?.[0]
  if (!file) return
  await uploadInlineImage(file, lang, null)
  event.target.value = ''
}

async function removeCoverImage() {
  const current = form.value.cover_image_url
  form.value.cover_image_url = ''
  if (current && pendingUploads.value.has(current)) {
    pendingUploads.value.delete(current)
    await deleteUploadedImage(current)
  }
}

function triggerCoverReplace() {
  coverInput.value?.click()
}

function previewHtmlWithResolvedMedia(value) {
  return value.replace(/<img src="([^"]+)"/g, (_match, src) => `<img src="${resolveMediaUrl(src)}"`)
}

async function deleteUploadedImage(url, showError = true) {
  if (!url) return
  try {
    await api.delete('/api/admin/forum-posts/uploaded-image', { data: { url } })
  } catch {
    if (showError) {
      toast.error(t('admin.error_deleting'))
    }
  }
}

function collectInlineUrls(bodyByLang) {
  const urls = new Set()
  for (const value of Object.values(bodyByLang || {})) {
    const text = (value || '').toString()
    const matches = text.match(/!\[[^\]]*\]\((https?:\/\/[^\s)]+|\/[^\s)]+)\)/g) || []
    for (const match of matches) {
      const urlMatch = match.match(/\((https?:\/\/[^\s)]+|\/[^\s)]+)\)/)
      if (urlMatch?.[1]) urls.add(urlMatch[1])
    }
  }
  return urls
}

async function cleanupPendingUploads() {
  if (!pendingUploads.value.size) return

  const referencedInline = collectInlineUrls(form.value.body)
  if (form.value.cover_image_url) {
    referencedInline.add(form.value.cover_image_url)
  }

  const toDelete = [...pendingUploads.value].filter((url) => !referencedInline.has(url))
  if (!toDelete.length) return

  await Promise.all(toDelete.map((url) => deleteUploadedImage(url, false)))
  toDelete.forEach((url) => pendingUploads.value.delete(url))
}

function toDateTimeLocal(value) {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  const yyyy = date.getFullYear()
  const mm = String(date.getMonth() + 1).padStart(2, '0')
  const dd = String(date.getDate()).padStart(2, '0')
  const hh = String(date.getHours()).padStart(2, '0')
  const mi = String(date.getMinutes()).padStart(2, '0')
  return `${yyyy}-${mm}-${dd}T${hh}:${mi}`
}

function slugify(value) {
  return (value || '')
    .toString()
    .normalize('NFKD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

watch(
  () => [form.value.title.en, form.value.title.lv, form.value.slug_locked, editing.value],
  () => {
    if (!editing.value || form.value.slug_locked) return
    const next = slugify(form.value.title.en || form.value.title.lv || '')
    form.value.slug = next
  },
)

const renderedPreview = computed(() => {
  const raw = form.value.body?.[activeBodyLang.value] || ''
  const escaped = raw
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
  const html = escaped
    .replace(/^### (.*)$/gm, '<h3>$1</h3>')
    .replace(/^## (.*)$/gm, '<h2>$1</h2>')
    .replace(/^# (.*)$/gm, '<h1>$1</h1>')
    .replace(/!\[(.*?)\]\((https?:\/\/[^\s)]+|\/[^\s)]+)\)/g, '<img src="$2" alt="$1" />')
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/\[(.*?)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>')
    .replace(/\n/g, '<br>')

  return previewHtmlWithResolvedMedia(html)
})

async function confirmDelete() {
  if (!confirmingDelete.value) return
  deletingNow.value = true
  try {
    await api.delete(`/api/admin/forum-posts/${confirmingDelete.value.id}`)
    toast.success(t('admin.deleted'))
    confirmingDelete.value = null
    await load()
  } catch {
    toast.error(t('admin.error_deleting'))
  } finally {
    deletingNow.value = false
  }
}
</script>

<template>
  <div>
    <div class="flex flex-wrap items-center gap-3 mb-4">
      <button class="btn-primary" @click="newOne">{{ t('admin.new_forum_post') }}</button>
      <div class="flex flex-wrap gap-1 font-sans text-xs uppercase tracking-wider">
        <button
          v-for="item in ['all', 'published', 'draft']"
          :key="item"
          @click="status = item"
          :class="[
            'px-3 py-1.5 border rounded-sm transition-colors',
            status === item ? 'border-gold-500 text-gold-500 bg-gold-500/5' : 'border-parchment-300 text-ink-500 hover:border-ink-500/40',
          ]"
        >
          {{ t(`admin.filter_${item}`) }}
        </button>
      </div>
      <input v-model="search" type="search" :placeholder="t('admin.search')" class="field-input !w-auto !py-1.5 text-sm md:min-w-[14rem] ml-auto" />
    </div>

    <div v-if="loading" class="card p-6 text-center text-ink-500 italic font-sans">{{ t('admin.loading') }}</div>
    <div v-else-if="!filtered.length" class="card p-6 text-center text-ink-500 italic font-sans">{{ t('admin.no_results') }}</div>
    <div v-else class="card overflow-x-auto">
      <table class="w-full text-sm font-sans">
        <thead class="bg-parchment-200/60 text-left">
          <tr>
            <th class="p-3">{{ t('admin.page_title') }}</th>
            <th class="p-3">{{ t('admin.status') }}</th>
            <th class="p-3">{{ t('admin.date') }}</th>
            <th class="p-3">{{ t('admin.tags') }}</th>
            <th class="p-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in filtered" :key="item.id" class="border-t border-parchment-300/50">
            <td class="p-3">
              <div class="font-semibold">{{ localTitle(item) }}</div>
              <div class="text-xs text-ink-500">{{ item.slug }}</div>
            </td>
            <td class="p-3">
              <span class="text-xs uppercase tracking-widest" :class="item.published ? 'text-gold-500' : 'text-ink-500'">
                {{ item.published ? t('admin.filter_published') : t('admin.filter_draft') }}
              </span>
              <span v-if="item.is_pinned" class="ml-2 text-[10px] uppercase tracking-widest text-gold-500 border border-gold-500/50 px-2 py-1 rounded-sm">
                {{ t('forum.pinned') }}
              </span>
            </td>
            <td class="p-3 text-ink-500">{{ new Date(item.published_at || item.updated_at).toLocaleDateString() }}</td>
            <td class="p-3">{{ (item.tags || []).join(', ') || '—' }}</td>
            <td class="p-3 text-right space-x-1 whitespace-nowrap">
              <button class="btn-ghost !px-2 !py-1 !text-[11px]" @click="edit(item)">{{ t('admin.edit') }}</button>
              <button class="btn-ghost !px-2 !py-1 !text-[11px] text-oxblood-500" @click="confirmingDelete = item">{{ t('admin.delete') }}</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Teleport to="body">
      <div v-if="editing" class="fixed inset-0 bg-ink-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="cancel">
        <div class="card p-6 sm:p-7 max-w-4xl w-full max-h-[90vh] overflow-y-auto">
          <h3 class="mb-5">{{ editing === 'new' ? t('admin.new_forum_post') : t('admin.edit') }}</h3>

          <div class="grid sm:grid-cols-2 gap-4 font-sans text-sm">
            <label>
              <span class="field-label">Slug</span>
              <input v-model="form.slug" class="field-input" @input="form.slug_locked = true" />
              <label class="mt-2 flex items-center gap-2 text-xs text-ink-500">
                <input v-model="form.slug_locked" type="checkbox" />
                {{ t('admin.lock_slug') }}
              </label>
            </label>
            <label>
              <span class="field-label">{{ t('admin.tags') }}</span>
              <input v-model="form.tags" class="field-input" :placeholder="t('admin.tags_hint')" />
            </label>
            <label class="sm:col-span-2">
              <span class="field-label">{{ t('admin.upload_cover') }}</span>
              <input
                ref="coverInput"
                type="file"
                accept="image/*"
                class="field-input"
                :disabled="uploadingCover"
                @change="onCoverFileChange"
              />
              <p v-if="uploadingCover" class="text-xs text-ink-500 mt-1">{{ t('admin.uploading') }}</p>
            </label>
            <div v-if="form.cover_image_url" class="sm:col-span-2">
              <img :src="resolveMediaUrl(form.cover_image_url)" alt="Cover preview" class="h-40 w-full object-cover rounded-sm border border-parchment-300/70" />
              <div class="mt-3 flex gap-2">
                <button type="button" class="btn-ghost !py-1.5 !px-3" @click="triggerCoverReplace">{{ t('admin.replace_image') }}</button>
                <button type="button" class="btn-ghost !py-1.5 !px-3 text-oxblood-500" @click="removeCoverImage">{{ t('admin.remove_image') }}</button>
              </div>
            </div>
            <div class="sm:col-span-2 flex flex-wrap items-center gap-4 mt-1">
              <label class="flex items-center gap-2 font-sans text-sm">
                <input v-model="form.published" type="checkbox" /> {{ t('admin.filter_published') }}
              </label>
              <label class="flex items-center gap-2 font-sans text-sm">
                <input v-model="form.is_pinned" type="checkbox" /> {{ t('forum.pinned') }}
              </label>
              <label class="font-sans text-sm">
                <span class="field-label !mb-1">{{ t('admin.publish_at') }}</span>
                <input v-model="form.published_at" type="datetime-local" class="field-input !py-2" />
              </label>
            </div>
          </div>

          <h4 class="mt-6 mb-2 uppercase text-xs tracking-widest text-ink-500 font-sans">Title (per language)</h4>
          <div class="grid sm:grid-cols-2 gap-2">
            <label v-for="l in langs" :key="'t-'+l" class="text-xs font-sans">
              <span class="text-ink-500">{{ l.toUpperCase() }}</span>
              <input v-model="form.title[l]" class="field-input !py-2 mt-1" />
            </label>
          </div>

          <h4 class="mt-5 mb-2 uppercase text-xs tracking-widest text-ink-500 font-sans">{{ t('admin.excerpt') }}</h4>
          <div class="grid sm:grid-cols-2 gap-2">
            <label v-for="l in langs" :key="'e-'+l" class="text-xs font-sans">
              <span class="text-ink-500">{{ l.toUpperCase() }}</span>
              <textarea v-model="form.excerpt[l]" rows="2" class="field-input !py-2 mt-1"></textarea>
            </label>
          </div>

          <h4 class="mt-5 mb-2 uppercase text-xs tracking-widest text-ink-500 font-sans">{{ t('admin.page_body') }}</h4>
          <div class="mb-2 flex flex-wrap items-center gap-2">
            <button type="button" class="btn-ghost !py-1 !px-2 !text-[11px]" @click="withSelection(activeBodyLang, '**', '**')"><b>B</b></button>
            <button type="button" class="btn-ghost !py-1 !px-2 !text-[11px]" @click="withSelection(activeBodyLang, '*', '*')"><i>I</i></button>
            <button type="button" class="btn-ghost !py-1 !px-2 !text-[11px]" @click="withSelection(activeBodyLang, '[', '](https://)')">{{ t('admin.link') }}</button>
            <button type="button" class="btn-ghost !py-1 !px-2 !text-[11px]" @click="withSelection(activeBodyLang, '## ', '')">H2</button>
          </div>
          <div class="grid sm:grid-cols-2 gap-2">
            <label v-for="l in langs" :key="'b-'+l" class="text-xs font-sans">
              <span class="text-ink-500">{{ l.toUpperCase() }}</span>
              <textarea
                v-model="form.body[l]"
                :ref="(el) => setBodyRef(l, el)"
                rows="7"
                class="field-input !py-2 mt-1"
                :placeholder="t('admin.drop_image_hint')"
                @focus="activeBodyLang = l"
                @drop="onBodyDrop($event, l)"
                @dragover="onBodyDragOver"
              ></textarea>
              <div class="mt-2 flex items-center gap-2">
                <label class="btn-ghost !py-1 !px-2 !text-[11px] cursor-pointer">
                  {{ t('admin.upload_inline_image') }}
                  <input type="file" accept="image/*" class="hidden" @change="onBodyFilePick($event, l)" />
                </label>
                <span v-if="uploadingInlineByLang[l]" class="text-[11px] text-ink-500">{{ t('admin.uploading') }}</span>
              </div>
            </label>
          </div>
          <div class="card p-3 mt-4">
            <div class="text-xs uppercase tracking-widest text-ink-500 mb-2">{{ t('admin.preview') }} ({{ activeBodyLang.toUpperCase() }})</div>
            <div class="prose-parchment leading-relaxed [&_img]:w-full [&_img]:rounded-sm [&_img]:border [&_img]:border-parchment-300/70 [&_a]:text-oxblood-500" v-html="renderedPreview"></div>
          </div>

          <div class="mt-6 flex gap-2 justify-end">
            <button class="btn-ghost" :disabled="saving" @click="cancel">{{ t('admin.cancel') }}</button>
            <button class="btn-ghost" :disabled="saving" @click="save(true)">{{ t('admin.save_copy_url') }}</button>
            <button class="btn-primary" :disabled="saving" @click="save(false)">{{ t('admin.save') }}</button>
          </div>
        </div>
      </div>
    </Teleport>

    <ConfirmDialog
      :open="!!confirmingDelete"
      destructive
      :title="t('admin.delete')"
      :message="t('admin.confirm_delete_forum_post')"
      :confirm-label="t('admin.delete')"
      :loading="deletingNow"
      @confirm="confirmDelete"
      @cancel="confirmingDelete = null"
    />
  </div>
</template>
