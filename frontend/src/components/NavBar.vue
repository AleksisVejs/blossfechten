<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { useRoute } from 'vue-router'
import LangSwitcher from './LangSwitcher.vue'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const open = ref(false)
const isScrolled = ref(false)
const accountMenuOpen = ref(false)

const mainLinks = [
  { name: 'about', label: 'nav.about' },
  { name: 'schedule', label: 'nav.schedule' },
  { name: 'forum', label: 'nav.forum' },
  { name: 'members', label: 'nav.members' },
  { name: 'contact', label: 'nav.contact' },
]

const utilityLinks = computed(() => {
  if (!auth.isAuthenticated) {
    return []
  }

  return [
    { name: 'profile', label: 'nav.profile' },
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

function closeAccountMenu() {
  accountMenuOpen.value = false
}

function isActive(name) {
  return route.name === name
}

function onWindowScroll() {
  isScrolled.value = window.scrollY > 8
}

onMounted(() => {
  onWindowScroll()
  window.addEventListener('scroll', onWindowScroll, { passive: true })
  window.addEventListener('click', onWindowClick)
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onWindowScroll)
  window.removeEventListener('click', onWindowClick)
})

watch(
  () => route.fullPath,
  () => {
    closeMenu()
    closeAccountMenu()
  },
)

function onWindowClick(event) {
  const target = event.target
  if (!(target instanceof Element)) {
    return
  }

  if (!target.closest('[data-account-menu]')) {
    closeAccountMenu()
  }
}

function navItemClass(name) {
  return [
    'rounded-full border border-transparent px-3 py-2 text-[13px] uppercase tracking-wide transition-all duration-150',
    isActive(name)
      ? 'border-oxblood-500/40 bg-oxblood-500 text-white shadow-sm'
      : 'text-ink-700 hover:border-parchment-300 hover:bg-parchment-200 hover:text-oxblood-500',
  ]
}

const utilityButtonClass =
  'hidden sm:inline-flex rounded-full px-3 py-2 text-[13px] uppercase tracking-wide text-ink-700 transition-colors duration-150 hover:bg-parchment-200 hover:text-oxblood-500'

const utilityButtonPrimaryClass =
  'hidden sm:inline-flex rounded-full bg-oxblood-500 px-3 py-2 text-[13px] uppercase tracking-wide text-white transition-colors duration-150 hover:bg-oxblood-600'

const utilityButtonLogoutClass =
  'hidden sm:inline-flex rounded-full border border-oxblood-300/70 bg-parchment-100 px-3 py-2 text-[13px] uppercase tracking-wide text-oxblood-600 transition-colors duration-150 hover:bg-parchment-200 hover:text-oxblood-700'

const accountButtonClass =
  'hidden lg:inline-flex items-center gap-1 rounded-full border border-parchment-300 bg-parchment-100 px-3 py-2 text-[13px] uppercase tracking-wide text-ink-800 transition-colors duration-150 hover:bg-parchment-200'
</script>

<template>
  <header class="sticky top-0 z-40 border-b border-parchment-300/60 bg-parchment-50/85 backdrop-blur-md">
    <div
      :class="[
        'mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 transition-all duration-200',
        isScrolled ? 'h-14' : 'h-16',
      ]"
    >
      <router-link :to="{ name: 'home' }" class="group flex shrink-0 items-center gap-2 sm:gap-3" @click="closeMenu">
        <img
          src="/img/logo.png"
          alt="Blossfechten Riga"
          :class="['w-10 object-contain transition-all duration-200', isScrolled ? 'h-8' : 'h-10']"
        />
        <div class="font-display text-base leading-none text-ink-900 sm:hidden">Blossfechten Riga</div>
        <div class="hidden sm:block">
          <div class="font-display leading-none text-xl text-ink-900">{{ $t('brand') }}</div>
          <div class="hidden text-xs uppercase tracking-[0.2em] text-ink-500 lg:block">Fencer's Guild Latvia</div>
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
          <div class="relative hidden lg:block" data-account-menu>
            <button :class="accountButtonClass" :aria-expanded="accountMenuOpen" aria-haspopup="menu" @click.stop="accountMenuOpen = !accountMenuOpen">
              {{ $t('nav.account') }}
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-150" :class="accountMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div
              v-if="accountMenuOpen"
              class="absolute right-0 top-11 z-50 min-w-48 rounded-xl border border-parchment-300/80 bg-parchment-50 p-2 shadow-lg"
              role="menu"
            >
              <router-link
                v-for="link in utilityLinks"
                :key="link.name"
                :to="{ name: link.name }"
                :class="[
                  'block rounded-lg px-3 py-2 text-xs uppercase tracking-wide transition-colors duration-150',
                  isActive(link.name) ? 'bg-oxblood-500 text-white' : 'text-ink-700 hover:bg-parchment-200 hover:text-oxblood-500',
                ]"
                role="menuitem"
                @click="closeAccountMenu"
              >
                {{ $t(link.label) }}
              </router-link>
              <button
                class="mt-1 block w-full rounded-lg border border-oxblood-300/70 bg-parchment-100 px-3 py-2 text-left text-xs uppercase tracking-wide text-oxblood-600 transition-colors duration-150 hover:bg-parchment-200 hover:text-oxblood-700"
                role="menuitem"
                @click="() => { closeAccountMenu(); doLogout() }"
              >
                {{ $t('nav.logout') }}
              </button>
            </div>
          </div>
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
      <p class="px-1 pb-2 text-xs font-medium uppercase tracking-[0.16em] text-ink-500">{{ $t('nav.main') }}</p>
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
          <p class="px-1 pb-2 text-xs font-medium uppercase tracking-[0.16em] text-ink-500">{{ $t('nav.account') }}</p>
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
