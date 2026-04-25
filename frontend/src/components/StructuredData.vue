<script setup>
import { computed } from 'vue'
import { useHead } from '@unhead/vue'

const props = defineProps({
  schema: { type: [Object, Array], required: true },
})

useHead({
  script: computed(() => [{
    type: 'application/ld+json',
    innerHTML: JSON.stringify(props.schema),
    key: 'structured-data',
  }]),
})
</script>

<template></template>
