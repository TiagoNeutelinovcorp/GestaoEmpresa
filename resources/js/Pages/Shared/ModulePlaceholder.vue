<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">{{ title }}</h1>
        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
          MVP
        </span>
      </div>

      <div class="rounded-lg border bg-white p-6 shadow-sm">
        <p class="text-sm text-gray-700">
          O módulo <strong>{{ title }}</strong> já está preparado no menu e no routing.
          Nesta fase fica pronto para receber os formulários Shadcn Vue e integrações avançadas.
        </p>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'

defineProps({
  title: {
    type: String,
    required: true
  }
})
</script>
