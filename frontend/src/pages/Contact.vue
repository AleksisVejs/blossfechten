<script setup>
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
const { t } = useI18n()

useHead({
  title: () => `${t('contact.title')} — Blossfechten Riga`,
  meta: [
    { name: 'description', content: () => `${t('contact.come_body')}` },
    { property: 'og:title', content: () => `${t('contact.title')} — Blossfechten Riga` },
  ],
})
</script>

<template>
  <section class="max-w-3xl mx-auto px-4 py-16">
    <h1 class="text-center">{{ t('contact.title') }}</h1>
    <div class="divider-engraved my-6 mx-auto w-1/3"></div>

    <div class="card p-8 mb-8 grid md:grid-cols-2 gap-8">
      <div>
        <h3 class="mb-3">{{ t('contact.come') }}</h3>
        <p class="text-ink-500">{{ t('contact.come_body') }}</p>
      </div>
      <div class="space-y-4 font-sans">
        <div>
          <div class="text-xs uppercase tracking-widest text-ink-500">{{ t('contact.phone') }}</div>
          <a href="tel:+37127442755" class="text-lg hover:text-oxblood-500">+371 27442755</a>
        </div>
        <div>
          <div class="text-xs uppercase tracking-widest text-ink-500">{{ t('contact.email') }}</div>
          <a href="mailto:viitinsh@gmail.com" class="text-lg hover:text-oxblood-500">viitinsh@gmail.com</a>
        </div>
        <div>
          <div class="text-xs uppercase tracking-widest text-ink-500 mb-1">{{ t('contact.follow') }}</div>
          <div class="flex gap-3">
            <a href="https://www.facebook.com/BlossfechtenRiga/" target="_blank" rel="noopener" class="btn-ghost text-xs">Facebook</a>
            <a href="https://www.instagram.com/blossfechten_riga/" target="_blank" rel="noopener" class="btn-ghost text-xs">Instagram</a>
            <a href="https://www.tiktok.com/@blossfechten_riga" target="_blank" rel="noopener" class="btn-ghost text-xs">TikTok</a>
          </div>
        </div>
      </div>
    </div>
    <div class="card overflow-hidden">
      <iframe
        title="Training location — Riga"
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2175.4!2d24.1052!3d56.9496!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46eed0029aa0bbb7%3A0x400cfcd68f2fe30!2sRiga%2C%20Latvia!5e0!3m2!1sen!2slv!4v1714000000000!5m2!1sen!2slv"
        width="100%"
        height="320"
        style="border:0;"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
      ></iframe>
    </div>
  </section>
</template>
