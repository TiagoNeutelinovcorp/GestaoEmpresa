<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Encomendas - Fornecedores</h1>
        <Button variant="default" @click="modalOpen = true">Nova Encomenda Fornecedor</Button>
      </div>
      <DataTable :columns="columns" :data="rows">
        <template #valor_total="{ item }">{{ Number(item.valor_total).toFixed(2) }} €</template>
        <template #acoes="{ item }">
          <Button size="sm" variant="outline" @click.stop="downloadPdf(item.id)">PDF</Button>
        </template>
      </DataTable>

      <FormModal
        :is-open="modalOpen"
        title="Nova Encomenda Fornecedor"
        confirm-text="Guardar"
        @close="modalOpen = false"
        @confirm="createSupplierOrder"
      >
        <div class="space-y-4">
          <div>
            <label class="mb-1 block text-sm font-medium">Fornecedor</label>
            <Select v-model="form.fornecedor_id" :options="fornecedores.map(f => ({ value: f.id, label: f.nome }))" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium">Data</label>
            <Input v-model="form.data_encomenda" type="date" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium">Estado</label>
            <Select
              v-model="form.estado"
              :options="[
                { value: 'rascunho', label: 'Rascunho' },
                { value: 'fechado', label: 'Fechado' }
              ]"
            />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium">Valor Total</label>
            <Input v-model="form.valor_total" type="number" min="0" step="0.01" placeholder="0.00" />
          </div>
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
const fornecedores = ref([])
const modalOpen = ref(false)
const form = ref({ fornecedor_id: '', data_encomenda: '', estado: 'rascunho', valor_total: '' })
const { pushToast } = useToast()
const columns = [
  { header: 'Data', accessorKey: 'data_encomenda' },
  { header: 'Número', accessorKey: 'numero' },
  { header: 'Fornecedor', accessorKey: 'fornecedor_nome' },
  { header: 'Valor Total', accessorKey: 'valor_total' },
  { header: 'Estado', accessorKey: 'estado' },
  { header: 'Ações', accessorKey: 'acoes' }
]

const load = async () => {
  const res = await axios.get('/encomendas-fornecedores')
  rows.value = res.data.data
}

const loadFornecedores = async () => {
  const res = await axios.get('/entidades', { params: { tipo: 'fornecedor' } })
  fornecedores.value = res.data.data
}

const createSupplierOrder = async () => {
  try {
    await axios.post('/encomendas-fornecedores', {
      fornecedor_id: Number(form.value.fornecedor_id),
      data_encomenda: form.value.data_encomenda || null,
      estado: form.value.estado || 'rascunho',
      valor_total: form.value.valor_total === '' ? null : Number(form.value.valor_total)
    })
    pushToast({ type: 'success', title: 'Encomendas', message: 'Encomenda de fornecedor criada.' })
    modalOpen.value = false
    form.value = { fornecedor_id: '', data_encomenda: '', estado: 'rascunho', valor_total: '' }
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: 'Encomendas', message: error?.response?.data?.message || 'Erro ao criar encomenda.' })
  }
}

const downloadPdf = (id) => window.open(`/api/v1/encomendas-fornecedores/${id}/pdf`, '_blank')

load()
loadFornecedores()
</script>
