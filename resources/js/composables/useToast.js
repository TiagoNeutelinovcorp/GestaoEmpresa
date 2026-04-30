import { ref } from 'vue'

const toasts = ref([])

export function useToast() {
  const pushToast = ({ type = 'info', title = '', message = '', duration = 3500 }) => {
    const id = `${Date.now()}-${Math.random().toString(36).slice(2)}`

    toasts.value.push({
      id,
      type,
      title,
      message
    })

    setTimeout(() => {
      removeToast(id)
    }, duration)
  }

  const removeToast = (id) => {
    toasts.value = toasts.value.filter((toast) => toast.id !== id)
  }

  return {
    toasts,
    pushToast,
    removeToast
  }
}
