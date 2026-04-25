<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import LangSwitcher from './LangSwitcher.vue'

const auth = useAuthStore()
const router = useRouter()
const open = ref(false)

async function doLogout() {
  await auth.logout()
  router.push({ name: 'home' })
}
</script>

<template>
  <header class="border-b border-parchment-300/60 bg-parchment-50/70 backdrop-blur sticky top-0 z-30">
    <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
      <router-link :to="{ name: 'home' }" class="flex items-center gap-3 group">
        <img src="/img/logo.png" alt="Blossfechten Riga" class="w-10 h-10 object-contain" />
        <div class="hidden md:block">
          <div class="font-display text-xl text-ink-900 leading-none">{{ $t('brand') }}</div>
          <div class="text-xs uppercase tracking-[0.2em] text-ink-500">Schola Meyeri</div>
        </div>
      </router-link>

      <nav class="hidden md:flex items-center gap-6 font-sans text-sm uppercase tracking-wider">
        <router-link :to="{ name: 'about' }" class="hover:text-oxblood-500">{{ $t('nav.about') }}</router-link>
        <router-link :to="{ name: 'meyer' }" class="hover:text-oxblood-500">{{ $t('nav.meyer') }}</router-link>
        <router-link :to="{ name: 'schedule' }" class="hover:text-oxblood-500">{{ $t('nav.schedule') }}</router-link>
        <router-link :to="{ name: 'members' }" class="hover:text-oxblood-500">{{ $t('nav.members') }}</router-link>
        <router-link :to="{ name: 'gallery' }" class="hover:text-oxblood-500">{{ $t('nav.gallery') }}</router-link>
        <router-link :to="{ name: 'contact' }" class="hover:text-oxblood-500">{{ $t('nav.contact') }}</router-link>
      </nav>

      <div class="flex items-center gap-3">
        <LangSwitcher />
        <template v-if="auth.isAuthenticated">
          <router-link :to="{ name: 'dashboard' }" class="hidden md:inline text-sm uppercase tracking-wider hover:text-oxblood-500">{{ $t('nav.dashboard') }}</router-link>
          <router-link v-if="auth.isAdmin" :to="{ name: 'admin' }" class="hidden md:inline text-sm uppercase tracking-wider hover:text-oxblood-500">{{ $t('nav.admin') }}</router-link>
          <button class="btn-ghost" @click="doLogout">{{ $t('nav.logout') }}</button>
        </template>
        <template v-else>
          <router-link :to="{ name: 'login' }" class="btn-ghost">{{ $t('nav.login') }}</router-link>
          <router-link :to="{ name: 'register' }" class="btn-primary hidden sm:inline-flex">{{ $t('nav.register') }}</router-link>
        </template>
        <button class="md:hidden p-2" @click="open = !open" aria-label="Menu">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
      </div>
    </div>

    <nav v-if="open" class="md:hidden border-t border-parchment-300/60 px-4 py-4 flex flex-col gap-3 font-sans uppercase tracking-wider text-sm">
      <router-link @click="open=false" :to="{ name: 'about' }">{{ $t('nav.about') }}</router-link>
      <router-link @click="open=false" :to="{ name: 'meyer' }">{{ $t('nav.meyer') }}</router-link>
      <router-link @click="open=false" :to="{ name: 'schedule' }">{{ $t('nav.schedule') }}</router-link>
      <router-link @click="open=false" :to="{ name: 'members' }">{{ $t('nav.members') }}</router-link>
      <router-link @click="open=false" :to="{ name: 'gallery' }">{{ $t('nav.gallery') }}</router-link>
      <router-link @click="open=false" :to="{ name: 'contact' }">{{ $t('nav.contact') }}</router-link>
      <router-link v-if="auth.isAuthenticated" @click="open=false" :to="{ name: 'dashboard' }">{{ $t('nav.dashboard') }}</router-link>
      <router-link v-if="auth.isAdmin" @click="open=false" :to="{ name: 'admin' }">{{ $t('nav.admin') }}</router-link>
    </nav>
  </header>
</template>
