<template>
  <MainLayout>
    <div class="space-y-4">
      <h1 class="text-2xl font-bold">Gestão de Acessos - Permissões</h1>
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
  { header: 'Nome do Grupo', accessorKey: 'name' },
  { header: 'Permissões', accessorKey: 'permissions_count' }
]

const load = async () => {
  const res = await axios.get('/access/roles')
  rows.value = res.data.map((r) => ({ ...r, permissions_count: r.permissions?.length || 0 }))
}

load()
</script>
