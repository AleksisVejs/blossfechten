<script setup>
import { reactive, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHead } from '@unhead/vue'
import api from '@/lib/api'
import EditablePageText from '@/components/EditablePageText.vue'
import EditableTextPlaceholder from '@/components/EditableTextPlaceholder.vue'
import { useEditablePages } from '@/composables/useEditablePages'

const { t, locale } = useI18n()

const slugs = ['contact-header', 'contact-come', 'contact-follow', 'contact-form-intro']
const { pages, pageLoaded, pagesLoaded, pageTitle, pageBody, fetchPages, onPageUpdated } = useEditablePages(slugs)

onMounted(async () => {
  await fetchPages()
})
const phoneNumber = '+371 27442755'
const emailAddress = 'viitinsh@gmail.com'
const trainingAddress = 'Ādmiņu iela 4, Rīga, LV-1009'
const mapsQuery = 'https://www.google.com/maps?q=%C4%80dmi%C5%86u+iela+4%2C+Latgales+priek%C5%A1pils%C4%93ta%2C+R%C4%ABga%2C+LV-1009%2C+Latvia'
const socialLinks = [
  { name: 'Facebook', href: 'https://www.facebook.com/BlossfechtenRiga/' },
  { name: 'Instagram', href: 'https://www.instagram.com/blossfechten_riga/' },
  { name: 'TikTok', href: 'https://www.tiktok.com/@blossfechten_riga' },
]
const form = reactive({
  name: '',
  email: '',
  message: '',
})
const state = reactive({
  sending: false,
  success: '',
  error: '',
  fieldErrors: {},
})

async function submitContactForm() {
  state.sending = true
  state.success = ''
  state.error = ''
  state.fieldErrors = {}

  try {
    await api.post('/api/contact', form)
    state.success = t('contact.form_success')
    state.error = ''
    form.name = ''
    form.email = ''
    form.message = ''
  } catch (error) {
    const response = error?.response
    const errors = response?.data?.errors || {}
    state.fieldErrors = errors
    state.success = ''
    state.error = Object.keys(errors).length ? '' : t('contact.form_error')
  } finally {
    state.sending = false
  }
}

useHead({
  title: () => `${t('contact.title')} — Blossfechten Riga`,
  meta: [
    { name: 'description', content: () => `${t('contact.come_body')}` },
    { property: 'og:title', content: () => `${t('contact.title')} — Blossfechten Riga` },
  ],
})
</script>

<template>
  <section class="max-w-6xl mx-auto px-4 py-10 sm:py-16">
    <h1 class="text-center">
      <span>{{ pageTitle('contact-header', 'contact.title') }}</span>
      <EditableTextPlaceholder v-if="!pageLoaded['contact-header']" width-class="w-2/3" centered />
      <EditablePageText
        v-if="pagesLoaded"
        slug="contact-header"
        field="title"
        :page="pages['contact-header']"
        @updated="onPageUpdated('contact-header', $event)"
      />
    </h1>
    <div class="divider-engraved my-6 mx-auto w-1/3"></div>
    <p class="text-center text-ink-500 max-w-2xl mx-auto mb-8 sm:mb-10">
      <span>{{ pageBody('contact-header', 'contact.come_body') }}</span>
      <EditableTextPlaceholder v-if="!pageLoaded['contact-header']" :lines="2" width-class="w-5/6" centered />
      <EditablePageText
        v-if="pagesLoaded"
        slug="contact-header"
        field="body"
        :page="pages['contact-header']"
        @updated="onPageUpdated('contact-header', $event)"
      />
    </p>

    <div class="grid lg:grid-cols-2 gap-8 items-start">
      <div>
        <div class="card p-6 sm:p-8 mb-8 grid md:grid-cols-2 gap-8">
          <div class="space-y-4">
            <h3 class="mb-3">
              <span>{{ pageTitle('contact-come', 'contact.come') }}</span>
              <EditableTextPlaceholder v-if="!pageLoaded['contact-come']" width-class="w-2/3" />
              <EditablePageText
                v-if="pagesLoaded"
                slug="contact-come"
                field="title"
                :page="pages['contact-come']"
                @updated="onPageUpdated('contact-come', $event)"
              />
            </h3>
            <p class="mt-3 font-sans text-oxblood-500">
              <span>{{ pageBody('contact-come', 'contact.first_training_free') }}</span>
              <EditableTextPlaceholder v-if="!pageLoaded['contact-come']" width-class="w-1/2" />
              <EditablePageText
                v-if="pagesLoaded"
                slug="contact-come"
                field="body"
                :page="pages['contact-come']"
                @updated="onPageUpdated('contact-come', $event)"
              />
            </p>
            <div class="flex flex-wrap gap-3 pt-2">
              <a :href="`tel:${phoneNumber.replace(/\s+/g, '')}`" class="btn-primary">{{ t('contact.phone') }}</a>
              <a :href="`mailto:${emailAddress}`" class="btn-ghost">{{ t('contact.email') }}</a>
              <a :href="mapsQuery" target="_blank" rel="noopener" class="btn-ghost">{{ t('contact.open_map') }}</a>
            </div>
          </div>
          <div class="space-y-5 font-sans">
            <div>
              <div class="text-xs uppercase tracking-widest text-ink-500">{{ t('contact.phone') }}</div>
              <a :href="`tel:${phoneNumber.replace(/\s+/g, '')}`" class="text-lg hover:text-oxblood-500">{{ phoneNumber }}</a>
            </div>
            <div>
              <div class="text-xs uppercase tracking-widest text-ink-500">{{ t('contact.email') }}</div>
              <a :href="`mailto:${emailAddress}`" class="text-lg hover:text-oxblood-500">{{ emailAddress }}</a>
            </div>
            <div>
              <div class="text-xs uppercase tracking-widest text-ink-500">{{ t('contact.address') }}</div>
              <a :href="mapsQuery" target="_blank" rel="noopener" class="text-lg hover:text-oxblood-500">{{ trainingAddress }}</a>
            </div>
            <div>
              <div class="text-xs uppercase tracking-widest text-ink-500 mb-1">
                <span>{{ pageTitle('contact-follow', 'contact.follow') }}</span>
                <EditableTextPlaceholder v-if="!pageLoaded['contact-follow']" width-class="w-24" />
                <EditablePageText
                  v-if="pagesLoaded"
                  slug="contact-follow"
                  field="title"
                  :page="pages['contact-follow']"
                  @updated="onPageUpdated('contact-follow', $event)"
                />
              </div>
              <div class="flex flex-wrap gap-3">
                <a
                  v-for="social in socialLinks"
                  :key="social.name"
                  :href="social.href"
                  target="_blank"
                  rel="noopener"
                  class="btn-ghost text-xs"
                >
                  {{ social.name }}
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="card overflow-hidden">
          <iframe
            :title="`Training location — ${trainingAddress}`"
            :src="`${mapsQuery}&output=embed`"
            width="100%"
            height="360"
            style="border:0;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
          ></iframe>
        </div>
      </div>
      <form class="card p-6 sm:p-8 space-y-4" @submit.prevent="submitContactForm">
        <h3>
          <span>{{ pageTitle('contact-form-intro', 'contact.form_title') }}</span>
          <EditableTextPlaceholder v-if="!pageLoaded['contact-form-intro']" width-class="w-2/3" />
          <EditablePageText
            v-if="pagesLoaded"
            slug="contact-form-intro"
            field="title"
            :page="pages['contact-form-intro']"
            @updated="onPageUpdated('contact-form-intro', $event)"
          />
        </h3>
        <p class="text-ink-500">
          <span>{{ pageBody('contact-form-intro', 'contact.form_intro') }}</span>
          <EditableTextPlaceholder v-if="!pageLoaded['contact-form-intro']" :lines="2" width-class="w-5/6" />
          <EditablePageText
            v-if="pagesLoaded"
            slug="contact-form-intro"
            field="body"
            :page="pages['contact-form-intro']"
            @updated="onPageUpdated('contact-form-intro', $event)"
          />
        </p>

        <div>
          <label for="contact-name" class="block text-xs uppercase tracking-widest text-ink-500 mb-1">{{ t('auth.name') }}</label>
          <input
            id="contact-name"
            v-model.trim="form.name"
            type="text"
            class="w-full rounded-md border border-parchment-300 bg-parchment-50 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gold-500/40"
            maxlength="120"
            required
          />
          <p v-if="state.fieldErrors.name" class="mt-1 text-sm text-oxblood-500">{{ state.fieldErrors.name[0] }}</p>
        </div>

        <div>
          <label for="contact-email" class="block text-xs uppercase tracking-widest text-ink-500 mb-1">{{ t('contact.email') }}</label>
          <input
            id="contact-email"
            v-model.trim="form.email"
            type="email"
            class="w-full rounded-md border border-parchment-300 bg-parchment-50 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gold-500/40"
            maxlength="190"
            required
          />
          <p v-if="state.fieldErrors.email" class="mt-1 text-sm text-oxblood-500">{{ state.fieldErrors.email[0] }}</p>
        </div>

        <div>
          <label for="contact-message" class="block text-xs uppercase tracking-widest text-ink-500 mb-1">{{ t('contact.form_message') }}</label>
          <textarea
            id="contact-message"
            v-model.trim="form.message"
            rows="5"
            class="w-full rounded-md border border-parchment-300 bg-parchment-50 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gold-500/40"
            minlength="10"
            maxlength="3000"
            required
          ></textarea>
          <p v-if="state.fieldErrors.message" class="mt-1 text-sm text-oxblood-500">{{ state.fieldErrors.message[0] }}</p>
        </div>

        <div class="flex items-center gap-3">
          <button type="submit" class="btn-primary" :disabled="state.sending">
            {{ state.sending ? t('auth.submitting') : t('contact.form_submit') }}
          </button>
          <p v-if="state.success" class="text-sm text-emerald-700">{{ state.success }}</p>
          <p v-else-if="state.error" class="text-sm text-oxblood-500">{{ state.error }}</p>
        </div>
      </form>
    </div>
  </section>
</template>
