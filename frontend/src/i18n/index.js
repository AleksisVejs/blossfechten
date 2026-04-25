import { createI18n } from 'vue-i18n'
import lv from './locales/lv.js'
import en from './locales/en.js'
import ru from './locales/ru.js'
import cs from './locales/cs.js'
import de from './locales/de.js'

export const SUPPORTED_LOCALES = [
  { code: 'lv', label: 'Latviešu', flag: '🇱🇻' },
  { code: 'en', label: 'English',  flag: '🇬🇧' },
  { code: 'ru', label: 'Русский',  flag: '🇷🇺' },
  { code: 'cs', label: 'Čeština',  flag: '🇨🇿' },
  { code: 'de', label: 'Deutsch',  flag: '🇩🇪' },
]

function detectLocale() {
  const saved = localStorage.getItem('locale')
  if (saved && SUPPORTED_LOCALES.some(l => l.code === saved)) return saved
  const browser = (navigator.language || 'lv').slice(0, 2).toLowerCase()
  return SUPPORTED_LOCALES.some(l => l.code === browser) ? browser : 'lv'
}

export const i18n = createI18n({
  legacy: false,
  locale: detectLocale(),
  fallbackLocale: 'en',
  messages: { lv, en, ru, cs, de },
})

export function setLocale(code) {
  if (!SUPPORTED_LOCALES.some(l => l.code === code)) return
  i18n.global.locale.value = code
  localStorage.setItem('locale', code)
  document.documentElement.setAttribute('lang', code)
}
