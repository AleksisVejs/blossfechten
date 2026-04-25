import { ref } from 'vue'

const toasts = ref([])
let nextId = 1

function push(kind, message, timeout = 4000) {
  const id = nextId++
  toasts.value.push({ id, kind, message })
  if (timeout > 0) {
    setTimeout(() => dismiss(id), timeout)
  }
  return id
}

function dismiss(id) {
  const i = toasts.value.findIndex(t => t.id === id)
  if (i !== -1) toasts.value.splice(i, 1)
}

export function useToast() {
  return {
    toasts,
    success: (msg, t) => push('success', msg, t),
    error: (msg, t) => push('error', msg, t),
    info: (msg, t) => push('info', msg, t),
    dismiss,
  }
}
