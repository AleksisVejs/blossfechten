import axios from 'axios'

// In production, never fall back to localhost — that hangs every visitor's
// browser until the request times out. If VITE_API_URL is unset in a prod
// build we use a same-origin relative path, which fails fast if the backend
// isn't there and lets the UI render its static fallback content immediately.
const baseURL = import.meta.env.VITE_API_URL
  || (import.meta.env.DEV ? 'http://localhost:8000' : '')

const api = axios.create({
  baseURL,
  withCredentials: true,
  withXSRFToken: true,
  timeout: 8000,
  headers: { Accept: 'application/json' },
})

let csrfPromise = null

function getCandidateCookieDomains(hostname) {
  if (!hostname) return []

  const domains = [hostname]
  const parts = hostname.split('.')
  if (parts.length >= 2) {
    const parentDomain = parts.slice(-2).join('.')
    if (!domains.includes(parentDomain)) domains.push(parentDomain)
  }
  return domains
}

function clearCookie(name) {
  const expires = 'Thu, 01 Jan 1970 00:00:00 GMT'
  document.cookie = `${name}=; expires=${expires}; path=/`

  const hostname = window.location?.hostname
  const domains = getCandidateCookieDomains(hostname)
  domains.forEach((domain) => {
    document.cookie = `${name}=; expires=${expires}; path=/; domain=${domain}`
    document.cookie = `${name}=; expires=${expires}; path=/; domain=.${domain}`
  })
}

function getXsrfToken() {
  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/)
  if (!match) return null
  try {
    return decodeURIComponent(match[1])
  } catch {
    // Broken/partially-encoded cookie value can happen on shared hosting.
    // Drop it so the next csrf-cookie request can mint a valid one.
    clearCookie('XSRF-TOKEN')
    return null
  }
}

export async function ensureCsrf() {
  // Re-fetch if no promise yet, or if the cookie has gone missing
  if (!csrfPromise || !getXsrfToken()) {
    csrfPromise = api.get('/sanctum/csrf-cookie')
  }
  return csrfPromise
}

api.interceptors.request.use(async (config) => {
  const method = (config.method || 'get').toLowerCase()
  if (['post', 'put', 'patch', 'delete'].includes(method)) {
    await ensureCsrf()
    // Manually inject the token — withXSRFToken is unreliable cross-subdomain
    const token = getXsrfToken()
    if (token) config.headers['X-XSRF-TOKEN'] = token
  }
  const locale = localStorage.getItem('locale')
  if (locale) config.headers['X-Locale'] = locale
  return config
})

// On 419 (stale/missing CSRF token), reset and retry the request once
api.interceptors.response.use(
  response => response,
  async error => {
    if (error.response?.status === 419 && !error.config._csrfRetried) {
      csrfPromise = null
      clearCookie('XSRF-TOKEN')
      await ensureCsrf()
      error.config._csrfRetried = true
      const token = getXsrfToken()
      if (token) error.config.headers['X-XSRF-TOKEN'] = token
      return api.request(error.config)
    }
    return Promise.reject(error)
  }
)

export default api
