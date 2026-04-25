import { defineConfig } from 'vite'
import { createRequire } from 'node:module'
import { fileURLToPath, URL } from 'node:url'

const require = createRequire(import.meta.url)
const vue = require('@vitejs/plugin-vue')

export default defineConfig({
  plugins: [vue.default ? vue.default() : vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: { port: 5173, host: 'localhost' },
})
