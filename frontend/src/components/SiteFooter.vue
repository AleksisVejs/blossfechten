<script setup>
const year = new Date().getFullYear()
</script>

<template>
  <footer class="mt-16 border-t border-parchment-300/60 bg-parchment-100/50">
    <div class="max-w-6xl mx-auto px-4 py-10 grid sm:grid-cols-2 md:grid-cols-3 gap-6 text-sm">
      <div>
        <div class="font-display text-2xl">{{ $t('brand') }}</div>
        <p class="text-ink-500 mt-2 italic">{{ $t('tagline') }}</p>
        <p class="text-ink-500 mt-2">{{ $t('footer.guild') }}</p>
      </div>
      <div>
        <h4 class="uppercase tracking-widest text-xs text-ink-500 mb-2">{{ $t('contact.title') }}</h4>
        <ul class="space-y-1">
          <li>📞 <a href="tel:+37127442755" class="hover:text-oxblood-500">+371 27442755</a></li>
          <li>✉ <a href="mailto:viitinsh@gmail.com" class="hover:text-oxblood-500">viitinsh@gmail.com</a></li>
          <li>📍 Riga, Latvia</li>
        </ul>
      </div>
      <div>
        <h4 class="uppercase tracking-widest text-xs text-ink-500 mb-2">{{ $t('contact.follow') }}</h4>
        <ul class="space-y-1">
          <li><a href="https://facebook.com" target="_blank" rel="noopener" class="hover:text-oxblood-500">Facebook</a></li>
          <li><a href="https://instagram.com" target="_blank" rel="noopener" class="hover:text-oxblood-500">Instagram</a></li>
          <li><a href="https://tiktok.com" target="_blank" rel="noopener" class="hover:text-oxblood-500">TikTok</a></li>
        </ul>
      </div>
    </div>
    <div class="divider-engraved"></div>
    <div class="max-w-6xl mx-auto px-4 py-4 text-xs text-ink-500 text-center">
      {{ $t('footer.rights', { year }) }}
    </div>
  </footer>
</template>
