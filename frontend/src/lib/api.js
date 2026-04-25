import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000',
  withCredentials: true,
  withXSRFToken: true,
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
