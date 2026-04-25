<script setup>
import { useHead } from '@unhead/vue'
import { useRoute } from 'vue-router'
import { computed } from 'vue'
import NavBar from '@/components/NavBar.vue'
import SiteFooter from '@/components/SiteFooter.vue'
import { SUPPORTED_LOCALES } from '@/i18n'

const route = useRoute()
const BASE_URL = 'https://blossfechten.lv'

useHead({
  link: computed(() =>
    SUPPORTED_LOCALES.map(l => ({
      rel: 'alternate',
      hreflang: l.code,
      href: `${BASE_URL}${route.path}`,
    })).concat({ rel: 'alternate', hreflang: 'x-default', href: `${BASE_URL}${route.path}` })
  ),
})
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <NavBar />
    <main class="flex-1">
      <router-view v-slot="{ Component, route }">
        <transition name="fade" mode="out-in">
          <component :is="Component" :key="route.path" />
        </transition>
      </router-view>
    </main>
    <SiteFooter />
  </div>
</template>

<style>
.fade-enter-active, .fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
