<script setup>
import { useI18n } from 'vue-i18n'

defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
  message: { type: String, default: '' },
  confirmLabel: { type: String, default: '' },
  cancelLabel: { type: String, default: '' },
  destructive: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
})
const emit = defineEmits(['confirm', 'cancel'])

const { t } = useI18n()
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink-900/60 backdrop-blur-sm"
      @click.self="emit('cancel')"
    >
      <div class="card max-w-sm w-full p-6 sm:p-7" role="alertdialog" aria-modal="true">
        <h3 v-if="title" class="text-xl mb-3">{{ title }}</h3>
        <p v-if="message" class="text-ink-500 text-sm font-sans mb-5">{{ message }}</p>
        <div class="flex gap-2 justify-end">
          <button
            type="button"
            class="btn-ghost"
            :disabled="loading"
            @click="emit('cancel')"
          >{{ cancelLabel || t('admin.cancel') }}</button>
          <button
            type="button"
            :class="destructive ? 'btn-primary !bg-oxblood-600 !border-oxblood-700 hover:!bg-oxblood-700' : 'btn-primary'"
            :disabled="loading"
            @click="emit('confirm')"
          >
            <svg v-if="loading" class="w-4 h-4 mr-2 animate-spin" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25" />
              <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
            </svg>
            {{ confirmLabel || t('admin.confirm') }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
