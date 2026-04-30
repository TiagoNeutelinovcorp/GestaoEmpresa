<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Financeiro - Faturas Fornecedores</h1>
        <Button variant="default" @click="modalOpen = true">Nova Fatura</Button>
      </div>
      <DataTable :columns="columns" :data="rows">
        <template #valor_total="{ item }">{{ Number(item.valor_total).toFixed(2) }} €</template>
      </DataTable>

      <FormModal :is-open="modalOpen" title="Nova Fatura Fornecedor" confirm-text="Guardar" @close="modalOpen = false" @confirm="save">
        <div class="space-y-3">
          <Input v-model="form.numero" placeholder="Número" />
          <Input v-model="form.data_fatura" type="date" />
          <Input v-model="form.data_vencimento" type="date" />
          <Select v-model="form.fornecedor_id" :options="fornecedores.map(f => ({ value: f.id, label: f.nome }))" />
          <Select v-model="form.encomenda_fornecedor_id" :options="encomendas.map(e => ({ value: e.id, label: e.numero }))" />
          <Input v-model="form.valor_total" type="number" min="0" placeholder="Valor total" />
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
import Input from '@/Components/ui/Input.vue'
import Select from '@/Components/ui/Select.vue'
import axios from '@/axios'
import { useToast } from '@/composables/useToast'

const rows = ref([])
const fornecedores = ref([])
const encomendas = ref([])
const modalOpen = ref(false)
const form = ref({
  numero: '',
  data_fatura: '',
  data_vencimento: '',
  fornecedor_id: '',
  encomenda_fornecedor_id: '',
  valor_total: '',
  estado: 'pendente_pagamento'
})
const { pushToast } = useToast()
const columns = [
  { header: 'Data', accessorKey: 'data_fatura' },
  { header: 'Número', accessorKey: 'numero' },
  { header: 'Fornecedor', accessorKey: 'fornecedor_nome' },
  { header: 'Encomenda', accessorKey: 'encomenda_numero' },
  { header: 'Valor Total', accessorKey: 'valor_total' },
  { header: 'Estado', accessorKey: 'estado' }
]

const load = async () => {
  const res = await axios.get('/faturas-fornecedores')
  rows.value = res.data.data
}

const loadOptions = async () => {
  const [fornRes, encRes] = await Promise.all([
    axios.get('/entidades', { params: { tipo: 'fornecedor' } }),
    axios.get('/encomendas-fornecedores')
  ])
  fornecedores.value = fornRes.data.data
  encomendas.value = encRes.data.data
}

const save = async () => {
  try {
    await axios.post('/faturas-fornecedores', {
      ...form.value,
      fornecedor_id: Number(form.value.fornecedor_id),
      encomenda_fornecedor_id: form.value.encomenda_fornecedor_id ? Number(form.value.encomenda_fornecedor_id) : null,
      valor_total: Number(form.value.valor_total || 0)
    })
    pushToast({ type: 'success', title: 'Faturas', message: 'Fatura criada com sucesso.' })
    modalOpen.value = false
    form.value = {
      numero: '',
      data_fatura: '',
      data_vencimento: '',
      fornecedor_id: '',
      encomenda_fornecedor_id: '',
      valor_total: '',
      estado: 'pendente_pagamento'
    }
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: 'Faturas', message: error?.response?.data?.message || 'Erro ao criar fatura.' })
  }
}

load()
loadOptions()
</script>
