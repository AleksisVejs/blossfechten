<script setup>
import { useHead } from '@unhead/vue'
import { useRoute } from 'vue-router'
import { computed } from 'vue'
import NavBar from '@/components/NavBar.vue'
import SiteFooter from '@/components/SiteFooter.vue'
import Toast from '@/components/Toast.vue'
import CookieConsent from '@/components/CookieConsent.vue'
import { SITE_URL } from '@/lib/site'

const route = useRoute()

// A self-referencing canonical, and nothing else. There used to be an hreflang
// alternate per locale here, but every one of them pointed at this same URL:
// language is chosen client-side and never appears in the path, so there are no
// per-language URLs to advertise. Declaring five alternates for one URL tells a
// crawler nothing it can act on — the canonical is the honest signal.
useHead({
  link: computed(() => [
    { rel: 'canonical', href: `${SITE_URL}${route.path}` },
  ]),
})
</script>

<template>
  <div class="min-h-screen flex flex-col overflow-x-hidden">
    <NavBar />
    <main class="flex-1 min-w-0">
      <router-view v-slot="{ Component, route }">
        <transition name="fade" mode="out-in">
          <component :is="Component" :key="route.path" />
        </transition>
      </router-view>
    </main>
    <SiteFooter />
    <Toast />
    <CookieConsent />
  </div>
</template>

<style>
.fade-enter-active, .fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
