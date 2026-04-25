<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import api from '@/lib/api'

const { locale, t } = useI18n()
const page = ref(null)
onMounted(async () => {
  try {
    const { data } = await api.get('/api/content/pages/about')
    page.value = data.data
  } catch { page.value = null }
})

useHead({
  title: () => `${t('about.title')} — Blossfechten Riga`,
  meta: [
    { name: 'description', content: () => `${t('about.founded')}. ${t('about.meyer_badge')}.` },
    { property: 'og:title', content: () => `${t('about.title')} — Blossfechten Riga` },
  ],
})
</script>

<template>
  <section class="max-w-3xl mx-auto px-4 py-16">
    <p class="uppercase tracking-[0.3em] text-xs text-gold-500 text-center">{{ t('about.founded') }}</p>
    <h1 class="text-center mt-2">{{ t('about.title') }}</h1>
    <div class="divider-engraved my-6 mx-auto w-1/3"></div>
    <div class="prose-parchment text-lg">
      <p v-if="page">{{ page.body?.[locale] || page.body?.en }}</p>
      <p v-else class="italic text-ink-500">{{ t('common.loading') }}</p>
    </div>
    <figure class="mt-10 flex flex-col items-center">
      <img src="/img/meyer/portrait.jpg" alt="Joachim Meyer"
        class="w-40 h-40 object-cover rounded-full border-4 border-gold-500/60 shadow-md sepia-[0.2]" />
      <figcaption class="mt-3 text-xs italic text-ink-500">Joachim Meyer, Freifechter (c. 1537–1571)</figcaption>
      <img src="/img/meyer/signature.jpg" alt="Signature of Joachim Meyer"
        class="h-10 mt-4 opacity-80" />
    </figure>
    <div class="mt-10 text-center">
      <span class="inline-block px-4 py-1 border border-gold-500 text-gold-500 uppercase tracking-[0.25em] text-xs">{{ t('about.meyer_badge') }}</span>
    </div>
  </section>
</template>
