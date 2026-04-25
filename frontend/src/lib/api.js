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
export async function ensureCsrf() {
  if (!csrfPromise) {
    csrfPromise = api.get('/sanctum/csrf-cookie')
  }
  return csrfPromise
}

api.interceptors.request.use(async (config) => {
  const method = (config.method || 'get').toLowerCase()
  if (['post', 'put', 'patch', 'delete'].includes(method)) {
    await ensureCsrf()
  }
  const locale = localStorage.getItem('locale')
  if (locale) config.headers['X-Locale'] = locale
  return config
})

export default api
