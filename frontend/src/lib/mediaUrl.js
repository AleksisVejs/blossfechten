import api from '@/lib/api'

function getApiOrigin() {
  const rawBase = api?.defaults?.baseURL
  if (typeof rawBase === 'string' && rawBase.trim()) {
    try {
      return new URL(rawBase, window.location.origin).origin
    } catch {
      return window.location.origin
    }
  }
  return window.location.origin
}

export function resolveMediaUrl(value) {
  if (typeof value !== 'string' || !value.trim()) return ''
  const input = value.trim()

  if (/^https?:\/\//i.test(input)) return input
  if (input.startsWith('/')) return `${getApiOrigin()}${input}`
  return input
}
