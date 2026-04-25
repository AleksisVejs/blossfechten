<script setup>
import { computed, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { useRoute } from 'vue-router'
import LangSwitcher from './LangSwitcher.vue'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const open = ref(false)

const mainLinks = [
  { name: 'about', label: 'nav.about' },
  { name: 'schedule', label: 'nav.schedule' },
  { name: 'members', label: 'nav.members' },
  { name: 'contact', label: 'nav.contact' },
]

const utilityLinks = computed(() => {
  if (!auth.isAuthenticated) {
    return []
  }

  return [
    { name: 'dashboard', label: 'nav.dashboard' },
    ...(auth.isAdmin ? [{ name: 'admin', label: 'nav.admin' }] : []),
  ]
})

async function doLogout() {
  await auth.logout()
  router.push({ name: 'home' })
}

function closeMenu() {
  open.value = false
}

function isActive(name) {
  return route.name === name
}

function navItemClass(name) {
  return [
    'rounded-full px-3 py-2 text-[13px] uppercase tracking-wide transition-colors duration-150',
    isActive(name)
      ? 'bg-oxblood-500 text-white'
      : 'text-ink-700 hover:bg-parchment-200 hover:text-oxblood-500',
  ]
}

const utilityButtonClass =
  'hidden sm:inline-flex rounded-full px-3 py-2 text-[13px] uppercase tracking-wide text-ink-700 transition-colors duration-150 hover:bg-parchment-200 hover:text-oxblood-500'

const utilityButtonPrimaryClass =
  'hidden sm:inline-flex rounded-full bg-oxblood-500 px-3 py-2 text-[13px] uppercase tracking-wide text-white transition-colors duration-150 hover:bg-oxblood-600'

const utilityButtonLogoutClass =
  'hidden sm:inline-flex rounded-full border border-oxblood-300/70 bg-parchment-100 px-3 py-2 text-[13px] uppercase tracking-wide text-oxblood-600 transition-colors duration-150 hover:bg-parchment-200 hover:text-oxblood-700'
</script>

<template>
  <header class="sticky top-0 z-40 border-b border-parchment-300/60 bg-parchment-50/85 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-3 px-4">
      <router-link :to="{ name: 'home' }" class="group flex shrink-0 items-center gap-3" @click="closeMenu">
        <img src="/img/logo.png" alt="Blossfechten Riga" class="h-10 w-10 object-contain" />
        <div class="hidden sm:block">
          <div class="font-display leading-none text-xl text-ink-900">{{ $t('brand') }}</div>
          <div class="hidden text-xs uppercase tracking-[0.2em] text-ink-500 lg:block">Schola Meyeri</div>
        </div>
      </router-link>

      <nav class="hidden items-center gap-1 font-sans lg:flex">
        <router-link
          v-for="link in mainLinks"
          :key="link.name"
          :to="{ name: link.name }"
          :class="navItemClass(link.name)"
        >
          {{ $t(link.label) }}
        </router-link>
      </nav>

      <div class="flex shrink-0 items-center gap-2">
        <template v-if="auth.isAuthenticated">
          <router-link
            v-for="link in utilityLinks"
            :key="link.name"
            :to="{ name: link.name }"
            :class="['hidden lg:inline-flex', navItemClass(link.name)]"
          >
            {{ $t(link.label) }}
          </router-link>
          <button :class="utilityButtonLogoutClass" @click="doLogout">
            {{ $t('nav.logout') }}
          </button>
        </template>
        <template v-else>
          <router-link :to="{ name: 'login' }" :class="utilityButtonClass">
            {{ $t('nav.login') }}
          </router-link>
          <router-link :to="{ name: 'register' }" :class="utilityButtonPrimaryClass">
            {{ $t('nav.register') }}
          </router-link>
        </template>
        <button
          class="-mr-2 rounded-md p-2 text-ink-700 transition-colors hover:bg-parchment-200 lg:hidden"
          @click="open = !open"
          :aria-label="open ? 'Close menu' : 'Open menu'"
          :aria-expanded="open"
          aria-controls="mobile-menu"
        >
          <svg v-if="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M6 18L18 6"/></svg>
        </button>
        <LangSwitcher />
      </div>
    </div>

    <nav
      v-if="open"
      id="mobile-menu"
      class="border-t border-parchment-300/60 bg-parchment-50 px-4 pb-4 pt-3 lg:hidden"
    >
      <div class="flex flex-col gap-1 rounded-xl border border-parchment-300/70 bg-parchment-100/80 p-2 font-sans text-sm uppercase tracking-wide">
        <router-link
          v-for="link in mainLinks"
          :key="link.name"
          @click="closeMenu"
          :to="{ name: link.name }"
          :class="[
            'rounded-lg px-3 py-2 transition-colors duration-150',
            isActive(link.name) ? 'bg-oxblood-500 text-white' : 'text-ink-700 hover:bg-parchment-200 hover:text-oxblood-500',
          ]"
        >
          {{ $t(link.label) }}
        </router-link>
      </div>
      <template v-if="auth.isAuthenticated">
        <div class="mt-3 flex flex-col gap-1 border-t border-parchment-300/60 pt-3 font-sans text-sm uppercase tracking-wide">
          <router-link
            v-for="link in utilityLinks"
            :key="link.name"
            @click="closeMenu"
            :to="{ name: link.name }"
            :class="[
              'rounded-lg px-3 py-2 transition-colors duration-150',
              isActive(link.name) ? 'bg-oxblood-500 text-white' : 'text-ink-700 hover:bg-parchment-200 hover:text-oxblood-500',
            ]"
          >
            {{ $t(link.label) }}
          </router-link>
          <button class="rounded-lg border border-oxblood-300/70 bg-parchment-100 px-3 py-2 text-left text-oxblood-600 transition-colors duration-150 hover:bg-parchment-200 hover:text-oxblood-700" @click="() => { closeMenu(); doLogout() }">
            {{ $t('nav.logout') }}
          </button>
        </div>
      </template>
      <template v-else>
        <div class="mt-3 flex flex-col gap-2 border-t border-parchment-300/60 pt-3">
          <router-link @click="closeMenu" :to="{ name: 'login' }" class="btn-ghost justify-center !py-2.5">
            {{ $t('nav.login') }}
          </router-link>
          <router-link @click="closeMenu" :to="{ name: 'register' }" class="btn-primary justify-center !py-2.5">
            {{ $t('nav.register') }}
          </router-link>
        </div>
      </template>
    </nav>
  </header>
</template>
