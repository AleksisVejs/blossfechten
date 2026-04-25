<script setup>
import { useToast } from '@/composables/useToast'

const { toasts, dismiss } = useToast()

const styles = {
  success: 'border-gold-500/60 text-ink-900 bg-parchment-50',
  error: 'border-oxblood-500/60 text-oxblood-700 bg-oxblood-500/5',
  info: 'border-ink-500/30 text-ink-700 bg-parchment-50',
}
</script>

<template>
  <Teleport to="body">
    <div class="fixed top-20 right-4 z-[60] flex flex-col gap-2 pointer-events-none w-[min(22rem,calc(100vw-2rem))]">
      <transition-group name="toast" tag="div" class="flex flex-col gap-2">
        <div
          v-for="t in toasts"
          :key="t.id"
          :class="['pointer-events-auto px-4 py-3 border rounded-sm shadow-md font-sans text-sm flex items-start gap-2', styles[t.kind] || styles.info]"
          role="status"
        >
          <span class="flex-1 break-words">{{ t.message }}</span>
          <button
            @click="dismiss(t.id)"
            class="text-ink-500 hover:text-ink-900 leading-none -mt-0.5"
            :aria-label="$t('common.close')"
          >×</button>
        </div>
      </transition-group>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all .25s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(20px); }
</style>
