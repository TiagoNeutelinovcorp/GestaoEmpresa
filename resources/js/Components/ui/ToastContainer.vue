<template>
  <div class="fixed right-4 top-4 z-[100] flex w-full max-w-sm flex-col gap-2">
    <div
      v-for="toast in toasts"
      :key="toast.id"
      class="rounded-lg border p-3 shadow-lg backdrop-blur"
      :class="toastClass(toast.type)"
    >
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-sm font-semibold">{{ toast.title || defaultTitle(toast.type) }}</p>
          <p class="mt-1 text-sm opacity-90">{{ toast.message }}</p>
        </div>
        <button class="text-xs opacity-70 hover:opacity-100" @click="removeToast(toast.id)">Fechar</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useToast } from '@/composables/useToast'

const { toasts, removeToast } = useToast()

const toastClass = (type) => {
  if (type === 'success') return 'border-emerald-700 bg-emerald-950/95 text-emerald-100'
  if (type === 'error') return 'border-red-700 bg-red-950/95 text-red-100'
  if (type === 'warning') return 'border-amber-700 bg-amber-950/95 text-amber-100'
  return 'border-neutral-700 bg-neutral-900/95 text-neutral-100'
}

const defaultTitle = (type) => {
  if (type === 'success') return 'Sucesso'
  if (type === 'error') return 'Erro'
  if (type === 'warning') return 'Atenção'
  return 'Informação'
}
</script>
