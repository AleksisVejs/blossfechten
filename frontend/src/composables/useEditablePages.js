import { computed, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'
import { loadCachedPage, saveCachedPage } from '@/lib/pageCache'

export function useEditablePages(slugs) {
  const { t, locale } = useI18n()
  const pages = reactive({})
  const pageLoaded = reactive({})

  for (const slug of slugs) {
    const cached = loadCachedPage(slug)
    if (cached) pages[slug] = cached
    pageLoaded[slug] = !!cached
  }

  const pagesLoaded = computed(() => slugs.every((slug) => pageLoaded[slug]))

  function resolveField(slug, field, fallbackKey) {
    const page = pages[slug]
    const value = page?.[field]?.[locale.value] || page?.[field]?.en

    if (value) return value
    if (!pageLoaded[slug]) return ''

    return fallbackKey ? t(fallbackKey) : ''
  }

  function applyPage(slug, page) {
    if (!page) return

    pages[slug] = page
    saveCachedPage(slug, page)
  }

  async function fetchPage(slug) {
    try {
      const { data } = await api.get(`/api/content/pages/${slug}`)
      applyPage(slug, data.data)
    } catch {
      // Missing or unpublished editable content should fall back to i18n copy.
    } finally {
      pageLoaded[slug] = true
    }
  }

  async function fetchPages() {
    // Always refresh from the API so edits on one device appear on others.
    // localStorage cache is only used for instant paint while the request runs.
    if (slugs.length === 1) {
      await fetchPage(slugs[0])
      return
    }

    try {
      const { data } = await api.get('/api/content/pages', {
        params: { slugs },
      })
      const fetchedPages = data?.data || {}

      slugs.forEach((slug) => {
        applyPage(slug, fetchedPages[slug] || null)
        pageLoaded[slug] = true
      })
    } catch {
      await Promise.all(slugs.map((slug) => fetchPage(slug)))
    }
  }

  function onPageUpdated(slug, data) {
    pages[slug] = data
    pageLoaded[slug] = true
    saveCachedPage(slug, data)
  }

  return {
    pages,
    pageLoaded,
    pagesLoaded,
    pageTitle: (slug, fallbackKey) => resolveField(slug, 'title', fallbackKey),
    pageBody: (slug, fallbackKey) => resolveField(slug, 'body', fallbackKey),
    fetchPages,
    onPageUpdated,
  }
}
