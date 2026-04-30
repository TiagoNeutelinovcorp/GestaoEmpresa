<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Financeiro - Conta Corrente Clientes</h1>
        <Button variant="default" @click="modalOpen = true">Novo Movimento</Button>
      </div>
      <DataTable :columns="columns" :data="rows" />

      <FormModal :is-open="modalOpen" title="Novo Movimento" confirm-text="Guardar" @close="modalOpen = false" @confirm="save">
        <div class="space-y-3">
          <Select v-model="form.cliente_id" :options="clientes.map(c => ({ value: c.id, label: c.nome }))" />
          <Input v-model="form.data" type="date" />
          <Input v-model="form.descricao" placeholder="Descrição" />
          <Input v-model="form.debito" type="number" min="0" placeholder="Débito" />
          <Input v-model="form.credito" type="number" min="0" placeholder="Crédito" />
        </div>
      </FormModal>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import DataTable from '@/Components/ui/DataTable.vue'
import Button from '@/Components/ui/Button.vue'
import FormModal from '@/Components/ui/FormModal.vue'
import Select from '@/Components/ui/Select.vue'
import Input from '@/Components/ui/Input.vue'
import axios from '@/axios'
import { useToast } from '@/composables/useToast'
const rows = ref([])
const clientes = ref([])
const modalOpen = ref(false)
const form = ref({ cliente_id: '', data: '', descricao: '', debito: '', credito: '' })
const { pushToast } = useToast()
const columns = [
  { header: 'Data', accessorKey: 'data' },
  { header: 'Cliente', accessorKey: 'cliente_nome' },
  { header: 'Descrição', accessorKey: 'descricao' },
  { header: 'Débito', accessorKey: 'debito' },
  { header: 'Crédito', accessorKey: 'credito' }
]
const load = async () => {
  const res = await axios.get('/conta-corrente-clientes')
  rows.value = res.data.data
}

const loadClientes = async () => {
  const res = await axios.get('/entidades', { params: { tipo: 'cliente' } })
  clientes.value = res.data.data
}

const save = async () => {
  try {
    await axios.post('/conta-corrente-clientes', {
      cliente_id: Number(form.value.cliente_id),
      data: form.value.data,
      descricao: form.value.descricao,
      debito: Number(form.value.debito || 0),
      credito: Number(form.value.credito || 0)
    })
    pushToast({ type: 'success', title: 'Conta Corrente', message: 'Movimento criado com sucesso.' })
    modalOpen.value = false
    form.value = { cliente_id: '', data: '', descricao: '', debito: '', credito: '' }
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: 'Conta Corrente', message: error?.response?.data?.message || 'Erro ao criar movimento.' })
  }
}

load()
loadClientes()
</script>
