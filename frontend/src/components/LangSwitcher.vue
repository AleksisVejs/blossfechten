<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { SUPPORTED_LOCALES, setLocale } from '@/i18n'

const { locale } = useI18n()
const open = ref(false)

function pick(code) {
  setLocale(code)
  open.value = false
}
</script>

<template>
  <div class="relative">
    <button
      class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-[13px] uppercase tracking-wide text-ink-700 transition-colors duration-150 hover:bg-parchment-200 hover:text-oxblood-500"
      @click="open = !open"
      :aria-expanded="open"
      aria-label="Switch language"
    >
      <span>{{ SUPPORTED_LOCALES.find(l => l.code === locale)?.flag }}</span>
      <span>{{ locale }}</span>
    </button>
    <ul v-if="open" class="absolute right-0 z-40 mt-2 w-44 rounded-xl border border-parchment-300 bg-parchment-50 p-1 shadow-lg">
      <li v-for="l in SUPPORTED_LOCALES" :key="l.code">
        <button
          @click="pick(l.code)"
          class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left transition-colors duration-150 hover:bg-parchment-100 hover:text-oxblood-500"
          :class="{ 'bg-parchment-100 text-oxblood-500': l.code === locale }"
        >
          <span>{{ l.flag }}</span>
          <span class="font-sans text-sm">{{ l.label }}</span>
        </button>
      </li>
    </ul>
  </div>
</template>
