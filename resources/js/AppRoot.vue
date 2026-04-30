<template>
  <RouterView />
  <ToastContainer />
</template>

<script setup>
import { RouterView } from 'vue-router'
import ToastContainer from '@/Components/ui/ToastContainer.vue'
</script>
