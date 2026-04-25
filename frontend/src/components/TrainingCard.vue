<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({ session: { type: Object, required: true } })
const emit = defineEmits(['register', 'unregister'])
const { locale, t } = useI18n()
const auth = useAuthStore()

const title = computed(() => props.session.title?.[locale.value] || props.session.title?.en || props.session.focus)
const description = computed(() => props.session.description?.[locale.value] || props.session.description?.en || '')
const start = computed(() => new Date(props.session.starts_at))
const end = computed(() => new Date(props.session.ends_at))
const seatsLeft = computed(() => Math.max(0, (props.session.capacity ?? 0) - (props.session.confirmed_count ?? 0)))

const dateFmt = new Intl.DateTimeFormat(locale.value, { weekday: 'long', day: '2-digit', month: 'long' })
const timeFmt = new Intl.DateTimeFormat(locale.value, { hour: '2-digit', minute: '2-digit' })
</script>

<template>
  <article class="card p-5 flex flex-col gap-3">
    <div class="flex items-baseline justify-between flex-wrap gap-2">
      <div>
        <h3 class="text-xl">{{ title }}</h3>
        <p class="text-sm text-ink-500">{{ dateFmt.format(start) }} · {{ timeFmt.format(start) }}–{{ timeFmt.format(end) }}</p>
      </div>
      <span v-if="session.cancelled" class="text-oxblood-500 text-xs uppercase tracking-widest">{{ t('schedule.cancelled') }}</span>
      <span v-else-if="seatsLeft === 0" class="text-oxblood-500 text-xs uppercase tracking-widest">{{ t('schedule.full') }}</span>
      <span v-else class="text-xs text-ink-500 uppercase tracking-widest">{{ t('schedule.seats_left', { n: seatsLeft }) }}</span>
    </div>
    <p v-if="description" class="text-sm text-ink-500">{{ description }}</p>
    <p class="text-xs uppercase tracking-widest text-ink-500">📍 {{ session.location }}</p>

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
