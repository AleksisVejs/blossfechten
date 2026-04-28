<script setup>
defineProps({
  lines: { type: Number, default: 1 },
  widthClass: { type: String, default: 'w-full' },
  centered: { type: Boolean, default: false },
})
</script>

<template>
  <span
    class="inline-flex max-w-full flex-col gap-2 align-middle"
    :class="centered ? 'items-center' : 'items-start'"
    aria-hidden="true"
  >
    <span
      v-for="line in lines"
      :key="line"
      class="block h-[0.9em] rounded bg-parchment-300/80 animate-pulse"
      :class="line === lines ? widthClass : 'w-full'"
    ></span>
  </span>
</template>
