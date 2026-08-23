<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  analyticsAvailable,
  consent,
  denyConsent,
  grantConsent,
} from '@/analytics'

const { t } = useI18n()

// No measurement id configured means nothing to consent to — asking anyway
// would be a dark pattern in reverse: a cookie banner for zero cookies.
//
// Read straight from the stored choice with no mounted-yet guard. The choice
// comes out of localStorage synchronously at module load, so it is already
// correct on the first render and there is no flash to hide behind a flag.
const visible = computed(() => analyticsAvailable() && consent.value === null)
</script>

<template>
  <Teleport to="body">
    <transition name="consent">
      <div
        v-if="visible"
        class="fixed inset-x-0 bottom-0 z-[70] p-3 sm:p-4 pointer-events-none"
      >
        <div
          class="card pointer-events-auto mx-auto max-w-3xl p-5 shadow-lg bg-parchment-50"
          role="dialog"
          aria-modal="false"
          :aria-label="t('cookies.title')"
        >
          <h2 class="text-lg mb-1.5">{{ t('cookies.title') }}</h2>
          <p class="font-sans text-sm text-ink-700 leading-relaxed">
            {{ t('cookies.body') }}
          </p>
          <div class="mt-4 flex flex-col sm:flex-row gap-2 sm:justify-end">
            <!-- Refusing is exactly as easy as accepting, and is the first
                 control in the DOM order for keyboard users. -->
            <button class="btn-ghost" @click="denyConsent">
              {{ t('cookies.decline') }}
            </button>
            <button class="btn-primary" @click="grantConsent">
              {{ t('cookies.accept') }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<style scoped>
.consent-enter-active, .consent-leave-active { transition: opacity .25s ease, transform .25s ease; }
.consent-enter-from, .consent-leave-to { opacity: 0; transform: translateY(12px); }
</style>
