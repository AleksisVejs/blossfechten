<template>
  <section class="max-w-xl mx-auto px-4 py-24 text-center">
    <h1 class="font-serif text-7xl">404</h1>
    <p class="italic text-ink-500 mt-4">Non est hic — the page you seek lies not on this path.</p>
    <router-link :to="{ name: 'home' }" class="btn-primary mt-8">Home</router-link>
  </section>
</template>
