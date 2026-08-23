import { ref } from 'vue'

const GA_ID = import.meta.env.VITE_GA_MEASUREMENT_ID
const STORAGE_KEY = 'cookie-consent'

// 'granted' | 'denied' | null (never asked)
export const consent = ref(readStoredConsent())

let loaded = false
// Page views that happened before a choice was made. If the visitor later
// accepts we still do not replay them — analytics must not describe a period
// the visitor had not yet agreed to be measured in. We only track the page
// they are on when they accept.
let currentPath = null
let currentTitle = null

function readStoredConsent() {
  try {
    const saved = localStorage.getItem(STORAGE_KEY)
    return saved === 'granted' || saved === 'denied' ? saved : null
  } catch {
    // Private mode or blocked storage — treat as "not asked" and never persist.
    return null
  }
}

function persist(value) {
  try {
    localStorage.setItem(STORAGE_KEY, value)
  } catch {}
}

/** True when analytics is even a possibility — no GA id means no banner. */
export function analyticsAvailable() {
  return !!GA_ID
}

export function consentDecided() {
  return consent.value !== null
}

function loadGtag() {
  if (loaded || !GA_ID) return
  loaded = true

  const script = document.createElement('script')
  script.async = true
  script.src = `https://www.googletagmanager.com/gtag/js?id=${GA_ID}`
  document.head.appendChild(script)

  window.dataLayer = window.dataLayer || []
  function gtag() { window.dataLayer.push(arguments) }
  window.gtag = gtag

  gtag('js', new Date())
  gtag('config', GA_ID, { send_page_view: false, anonymize_ip: true })
}

export function grantConsent() {
  consent.value = 'granted'
  persist('granted')
  loadGtag()
  // Count the page they were on when they accepted, and nothing before it.
  if (currentPath) trackPageView(currentPath, currentTitle)
}

export function denyConsent() {
  consent.value = 'denied'
  persist('denied')
}

/**
 * Called once at boot. Nothing is loaded until the visitor has actually said
 * yes — Google Analytics sets identifying cookies, so under GDPR/ePrivacy the
 * script cannot be injected on the strength of "they did not object yet".
 */
export function initAnalytics() {
  if (consent.value === 'granted') loadGtag()
}

export function trackPageView(path, title) {
  currentPath = path
  currentTitle = title

  if (consent.value !== 'granted') return
  if (!GA_ID || typeof window.gtag !== 'function') return

  window.gtag('event', 'page_view', {
    page_path: path,
    page_title: title,
    page_location: window.location.href,
  })
}
