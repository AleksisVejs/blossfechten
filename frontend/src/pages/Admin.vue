<script setup>
import { ref, computed, defineAsyncComponent, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import { useRoute, useRouter } from 'vue-router'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()

const AdminTrainings = defineAsyncComponent(() => import('./admin/AdminTrainings.vue'))
const AdminUsers = defineAsyncComponent(() => import('./admin/AdminUsers.vue'))
const AdminMembers = defineAsyncComponent(() => import('./admin/AdminMembers.vue'))

const tab = ref('trainings')

const tabs = computed(() => [
  { key: 'trainings', label: t('admin.trainings'), comp: AdminTrainings },
  { key: 'members', label: t('admin.members_content'), comp: AdminMembers },
  { key: 'users', label: t('admin.users'), comp: AdminUsers },
])

const validTabs = computed(() => tabs.value.map((tItem) => tItem.key))

function normalizeTab(tabValue) {
  return validTabs.value.includes(tabValue) ? tabValue : 'trainings'
}

watch(
  () => route.query.tab,
  (queryTab) => {
    const nextTab = normalizeTab(typeof queryTab === 'string' ? queryTab : 'trainings')
    if (tab.value !== nextTab) {
      tab.value = nextTab
    }
  },
  { immediate: true },
)

watch(tab, (nextTab) => {
  if (route.query.tab !== nextTab) {
    router.replace({ query: { ...route.query, tab: nextTab } })
  }
})

useHead({
  title: () => `${t('admin.title')} — Blossfechten Riga`,
  meta: [{ name: 'robots', content: 'noindex' }],
})
</script>

<template>
  <section class="max-w-6xl mx-auto px-4 py-8 sm:py-12">
    <h1>{{ t('admin.title') }}</h1>
    <div class="divider-engraved my-6 w-1/3"></div>

    <div class="flex flex-wrap gap-1 mb-6 font-sans text-xs sm:text-sm uppercase tracking-wider border-b border-parchment-300/60">
      <button
        v-for="ti in tabs"
        :key="ti.key"
        @click="tab = ti.key"
        :class="['px-4 py-2.5 -mb-px border-b-2 transition-colors',
          tab === ti.key
            ? 'border-gold-500 text-ink-900'
            : 'border-transparent text-ink-500 hover:text-ink-900']"
      >{{ ti.label }}</button>
    </div>

    <component :is="tabs.find(ti => ti.key === tab)?.comp" />
  </section>
</template>
