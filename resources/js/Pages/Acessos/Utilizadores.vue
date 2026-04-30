<template>
  <MainLayout>
    <div class="space-y-4">
      <h1 class="text-2xl font-bold">Gestão de Acessos - Utilizadores</h1>
      <DataTable :columns="columns" :data="rows">
        <template #role_name="{ item }">
          <div class="flex items-center gap-2">
            <Select
              v-model="roleDraft[item.id]"
              :options="roles.map(r => ({ value: r.name, label: r.name }))"
            />
            <Button size="sm" variant="secondary" @click.stop="saveRole(item)">Guardar</Button>
          </div>
        </template>
      </DataTable>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import DataTable from '@/Components/ui/DataTable.vue'
import Select from '@/Components/ui/Select.vue'
import Button from '@/Components/ui/Button.vue'
import axios from '@/axios'
import { useToast } from '@/composables/useToast'

const rows = ref([])
const roles = ref([])
const roleDraft = ref({})
const { pushToast } = useToast()
const columns = [
  { header: 'Nome', accessorKey: 'name' },
  { header: 'Email', accessorKey: 'email' },
  { header: 'Telemóvel', accessorKey: 'telemovel' },
  { header: 'Grupo de Permissões', accessorKey: 'role_name' },
  { header: 'Estado', accessorKey: 'estado_label' }
]

const load = async () => {
  const [usersRes, rolesRes] = await Promise.all([
    axios.get('/access/users'),
    axios.get('/access/roles')
  ])
  roles.value = rolesRes.data || []
  rows.value = usersRes.data.data.map((u) => ({
    ...u,
    role_name: u.roles?.[0]?.name || '-',
    estado_label: u.estado ? 'Ativo' : 'Inativo'
  }))
  roleDraft.value = Object.fromEntries(rows.value.map((u) => [u.id, u.roles?.[0]?.name || 'Basico']))
}

const saveRole = async (user) => {
  try {
    await axios.put(`/access/users/${user.id}`, { role: roleDraft.value[user.id] })
    pushToast({ type: 'success', title: 'Utilizadores', message: 'Cargo atualizado com sucesso.' })
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: 'Utilizadores', message: error?.response?.data?.message || 'Erro ao atualizar cargo.' })
  }
}

load()
</script>
