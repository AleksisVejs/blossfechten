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
    <button class="flex items-center gap-2 px-2 py-1 border border-parchment-300 rounded-sm text-sm hover:bg-parchment-100" @click="open = !open">
      <span>{{ SUPPORTED_LOCALES.find(l => l.code === locale)?.flag }}</span>
      <span class="uppercase tracking-widest">{{ locale }}</span>
    </button>
    <ul v-if="open" class="absolute right-0 mt-2 w-44 bg-parchment-50 border border-parchment-300 rounded-sm shadow-lg z-40">
      <li v-for="l in SUPPORTED_LOCALES" :key="l.code">
        <button
          @click="pick(l.code)"
          class="w-full flex items-center gap-2 px-3 py-2 hover:bg-parchment-100"
          :class="{ 'bg-parchment-100': l.code === locale }">
          <span>{{ l.flag }}</span>
          <span class="font-sans text-sm">{{ l.label }}</span>
        </button>
      </li>
    </ul>
  </div>
</template>
