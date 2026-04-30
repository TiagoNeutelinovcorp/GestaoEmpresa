<template>
  <MainLayout>
    <div class="space-y-4">
      <h1 class="text-2xl font-bold">Configurações - Logs</h1>
      <DataTable :columns="columns" :data="rows" />
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import DataTable from '@/Components/ui/DataTable.vue'
import axios from '@/axios'
const rows = ref([])
const columns = [
  { header: 'Data', accessorKey: 'created_at' },
  { header: 'Utilizador', accessorKey: 'causer_id' },
  { header: 'Menu', accessorKey: 'menu' },
  { header: 'Ação', accessorKey: 'action' },
  { header: 'Dispositivo', accessorKey: 'device' },
  { header: 'IP', accessorKey: 'ip' }
]

axios.get('/logs').then((res) => {
  rows.value = res.data.data.map((r) => ({
    ...r,
    menu: r.properties?.menu,
    action: r.properties?.action,
    device: r.properties?.device,
    ip: r.properties?.ip
  }))
})
</script>
