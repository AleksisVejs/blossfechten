<script setup>
const year = new Date().getFullYear()
</script>

<template>
  <footer class="mt-16 border-t border-parchment-300/60 bg-parchment-100/50">
    <div class="max-w-6xl mx-auto px-4 py-8 sm:py-10 grid sm:grid-cols-2 md:grid-cols-3 gap-6 text-sm break-words">
      <div>
        <div class="font-display text-2xl">{{ $t('brand') }}</div>
        <p class="text-ink-500 mt-2 italic">{{ $t('tagline') }}</p>
        <p class="text-ink-500 mt-2">{{ $t('footer.guild') }}</p>
      </div>
      <div>
        <h4 class="uppercase tracking-widest text-xs text-ink-500 mb-2">{{ $t('contact.title') }}</h4>
        <ul class="space-y-1">
          <li class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-ink-500 shrink-0" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.11 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.72c.12.9.35 1.78.68 2.61a2 2 0 0 1-.45 2.11L8.1 9.67a16 16 0 0 0 6.23 6.23l1.23-1.23a2 2 0 0 1 2.11-.45c.83.33 1.71.56 2.61.68A2 2 0 0 1 22 16.92z" />
            </svg>
            <a href="tel:+37127442755" class="hover:text-oxblood-500">+371 27442755</a>
          </li>
          <li class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-ink-500 shrink-0" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M4 4h16a2 2 0 0 1 2 2v.4l-10 6.25L2 6.4V6a2 2 0 0 1 2-2z" />
              <path d="M22 8.1V18a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.1" />
            </svg>
            <a href="mailto:viitinsh@gmail.com" class="hover:text-oxblood-500">viitinsh@gmail.com</a>
          </li>
          <li class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-ink-500 shrink-0" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M12 22s7-6.16 7-12a7 7 0 1 0-14 0c0 5.84 7 12 7 12z" />
              <circle cx="12" cy="10" r="3" />
            </svg>
            <span>Riga, Latvia</span>
          </li>
        </ul>
      </div>
      <div>
        <h4 class="uppercase tracking-widest text-xs text-ink-500 mb-2">{{ $t('contact.follow') }}</h4>
        <ul class="space-y-1">
          <li><a href="https://www.facebook.com/BlossfechtenRiga/" target="_blank" rel="noopener" class="hover:text-oxblood-500">Facebook</a></li>
          <li><a href="https://www.instagram.com/blossfechten_riga/" target="_blank" rel="noopener" class="hover:text-oxblood-500">Instagram</a></li>
          <li><a href="https://www.tiktok.com/@blossfechten_riga" target="_blank" rel="noopener" class="hover:text-oxblood-500">TikTok</a></li>
        </ul>
      </div>
    </div>
    <div class="divider-engraved"></div>
    <div class="max-w-6xl mx-auto px-4 py-4 text-xs text-ink-500 text-center">
      {{ $t('footer.rights', { year }) }}
    </div>
  </footer>
</template>
