<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { parseLocalDateTime } from '@/lib/datetime'

const props = defineProps({ session: { type: Object, required: true } })
const emit = defineEmits(['register', 'unregister'])
const { locale, t } = useI18n()
const auth = useAuthStore()

const title = computed(() => props.session.title?.[locale.value] || props.session.title?.en || props.session.focus)
const description = computed(() => props.session.description?.[locale.value] || props.session.description?.en || '')
const start = computed(() => parseLocalDateTime(props.session.starts_at))
const end = computed(() => parseLocalDateTime(props.session.ends_at))
const seatsLeft = computed(() => Math.max(0, (props.session.capacity ?? 0) - (props.session.confirmed_count ?? 0)))

const dateTimeFmt = new Intl.DateTimeFormat('en-GB', {
  day: '2-digit',
  month: '2-digit',
  year: 'numeric',
  hour: '2-digit',
  minute: '2-digit',
  hour12: false,
})

const timeFmt = new Intl.DateTimeFormat('en-GB', {
  hour: '2-digit',
  minute: '2-digit',
  hour12: false,
})

const sameDay = computed(() => {
  const s = start.value, e = end.value
  return s.getFullYear() === e.getFullYear()
    && s.getMonth() === e.getMonth()
    && s.getDate() === e.getDate()
})

const when = computed(() => sameDay.value
  ? `${dateTimeFmt.format(start.value)}-${timeFmt.format(end.value)}`
  : `${dateTimeFmt.format(start.value)} - ${dateTimeFmt.format(end.value)}`
)
</script>

<template>
  <article class="card p-5 flex flex-col gap-3">
    <div class="flex items-baseline justify-between flex-wrap gap-2">
      <div>
        <h3 class="text-xl">{{ title }}</h3>
        <p class="text-sm text-ink-500">{{ when }}</p>
      </div>
      <span v-if="session.cancelled" class="text-oxblood-500 text-xs uppercase tracking-widest">{{ t('schedule.cancelled') }}</span>
      <span v-else-if="seatsLeft === 0" class="text-oxblood-500 text-xs uppercase tracking-widest">{{ t('schedule.full') }}</span>
      <span v-else class="text-xs text-ink-500 uppercase tracking-widest">{{ t('schedule.seats_left', { n: seatsLeft }) }}</span>
    </div>
    <p v-if="description" class="text-sm text-ink-500">{{ description }}</p>
    <p class="text-xs uppercase tracking-widest text-ink-500 flex items-center gap-1.5">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M12 22s7-6.16 7-12a7 7 0 1 0-14 0c0 5.84 7 12 7 12z" />
        <circle cx="12" cy="10" r="3" />
      </svg>
      <span>{{ session.location }}</span>
    </p>

    <div class="pt-2 flex gap-2">
      <template v-if="auth.isAuthenticated">
        <button v-if="!session.is_registered && !session.cancelled" class="btn-primary" @click="emit('register', session)">
          {{ seatsLeft === 0 ? t('schedule.waitlist') : t('schedule.register') }}
        </button>
        <button v-else-if="session.is_registered" class="btn-ghost" @click="emit('unregister', session)">
          {{ t('schedule.unregister') }}
        </button>
      </template>
      <router-link v-else :to="{ name: 'login' }" class="btn-ghost">{{ t('schedule.login_to_register') }}</router-link>
    </div>
  </article>
</template>
