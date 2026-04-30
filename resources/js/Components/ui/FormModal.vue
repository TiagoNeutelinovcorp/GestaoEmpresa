<template>
  <Teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/70" @click="close"></div>

      <!-- Modal -->
      <div class="relative z-10 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg border border-neutral-800 bg-neutral-950 text-neutral-100 shadow-lg">
        <div class="flex items-center justify-between border-b border-neutral-800 p-4">
          <h2 class="text-lg font-semibold">{{ title }}</h2>
          <button @click="close" class="text-neutral-400 hover:text-neutral-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-4">
          <slot />
        </div>

        <div class="flex justify-end gap-2 border-t border-neutral-800 p-4">
          <button
            @click="close"
            class="rounded-md bg-neutral-800 px-4 py-2 text-sm font-medium text-neutral-100 hover:bg-neutral-700"
          >
            Cancelar
          </button>
          <button
            @click="$emit('confirm')"
            class="rounded-md bg-neutral-100 px-4 py-2 text-sm font-medium text-neutral-900 hover:bg-neutral-300"
          >
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  isOpen: Boolean,
  title: String,
  confirmText: {
    type: String,
    default: 'Confirmar'
  }
})

const emit = defineEmits(['close', 'confirm'])

const close = () => {
  emit('close')
}
</script>
