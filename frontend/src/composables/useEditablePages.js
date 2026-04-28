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
    const missingSlugs = slugs.filter((slug) => !pageLoaded[slug])

    if (!missingSlugs.length) {
      return
    }

    if (missingSlugs.length === 1) {
      await fetchPage(missingSlugs[0])
      return
    }

    try {
      const { data } = await api.get('/api/content/pages', {
        params: { slugs: missingSlugs },
      })
      const fetchedPages = data?.data || {}

      missingSlugs.forEach((slug) => {
        applyPage(slug, fetchedPages[slug] || null)
      })
    } catch {
      await Promise.all(missingSlugs.map((slug) => fetchPage(slug)))
      return
    }

    missingSlugs.forEach((slug) => {
      pageLoaded[slug] = true
    })
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
