<template>
  <MainLayout>
    <div class="space-y-4">
      <h1 class="text-2xl font-bold">Dashboard</h1>
      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded border border-neutral-800 bg-black p-4">
          <p class="text-sm text-neutral-400">Clientes</p>
          <p class="text-2xl font-bold">{{ metrics.clientes }}</p>
        </div>
        <div class="rounded border border-neutral-800 bg-black p-4">
          <p class="text-sm text-neutral-400">Fornecedores</p>
          <p class="text-2xl font-bold">{{ metrics.fornecedores }}</p>
        </div>
        <div class="rounded border border-neutral-800 bg-black p-4">
          <p class="text-sm text-neutral-400">Propostas</p>
          <p class="text-2xl font-bold">{{ metrics.propostas }}</p>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { reactive } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import axios from '@/axios'

const metrics = reactive({ clientes: 0, fornecedores: 0, propostas: 0 })

const load = async () => {
  const [clientes, fornecedores, propostas] = await Promise.all([
    axios.get('/entidades', { params: { tipo: 'cliente' } }),
    axios.get('/entidades', { params: { tipo: 'fornecedor' } }),
    axios.get('/propostas')
  ])
  metrics.clientes = clientes.data.total || clientes.data.data.length
  metrics.fornecedores = fornecedores.data.total || fornecedores.data.data.length
  metrics.propostas = propostas.data.total || propostas.data.data.length
}

load()
</script>
