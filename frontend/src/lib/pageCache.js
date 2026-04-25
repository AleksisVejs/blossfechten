const PREFIX = 'page-cache:'

export function loadCachedPage(slug) {
  try {
    const raw = localStorage.getItem(PREFIX + slug)
    return raw ? JSON.parse(raw) : null
  } catch {
    return null
  }
}

export function saveCachedPage(slug, page) {
  try {
    if (page) localStorage.setItem(PREFIX + slug, JSON.stringify(page))
  } catch {}
}

const API_PREFIX = 'api-cache:'

export function loadCachedApi(key) {
  try {
    const raw = localStorage.getItem(API_PREFIX + key)
    return raw ? JSON.parse(raw) : null
  } catch {
    return null
  }
}

export function saveCachedApi(key, value) {
  try {
    if (value !== undefined && value !== null) {
      localStorage.setItem(API_PREFIX + key, JSON.stringify(value))
    }
  } catch {}
}
