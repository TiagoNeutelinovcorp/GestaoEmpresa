<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Encomendas - Clientes</h1>
        <Button variant="default" @click="openModal">Nova Encomenda Cliente</Button>
      </div>

      <DataTable :columns="columns" :data="rows">
        <template #validade="{ item }">{{ item.validade || '-' }}</template>
        <template #valor_total="{ item }">{{ Number(item.valor_total).toFixed(2) }} €</template>
        <template #acoes="{ item }">
          <div class="flex gap-2">
            <Button size="sm" variant="outline" @click.stop="downloadPdf(item.id)">PDF</Button>
            <Button
              size="sm"
              variant="secondary"
              :disabled="item.estado !== 'fechado'"
              @click.stop="toSuppliers(item.id)"
            >
              Gerar Enc. Fornecedor
            </Button>
          </div>
        </template>
      </DataTable>

      <FormModal
        :is-open="modalOpen"
        title="Nova Encomenda Cliente"
        confirm-text="Guardar"
        @close="modalOpen = false"
        @confirm="createOrder"
      >
        <div class="space-y-4">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium">Número</label>
              <Input :model-value="'Gerado automaticamente'" disabled />
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
              <label class="mb-1 block text-sm font-medium">Data da Encomenda</label>
              <Input v-model="form.data_encomenda" type="date" />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium">Validade</label>
              <Input :model-value="'N/A'" disabled />
            </div>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium">Cliente</label>
            <Select v-model="form.cliente_id" :options="clientes.map(c => ({ value: c.id, label: c.nome }))" />
          </div>

          <div class="rounded-md border border-neutral-800 p-3">
            <div class="mb-3 flex items-center justify-between">
              <h3 class="text-sm font-semibold">Linhas dos Artigos</h3>
              <Button size="sm" type="button" @click="addLine">Adicionar Linha</Button>
            </div>

            <div v-if="form.linhas.length === 0" class="text-sm text-neutral-400">
              Sem linhas. Adiciona pelo menos um artigo.
            </div>

            <div v-for="(linha, idx) in form.linhas" :key="idx" class="mb-3 rounded-md border border-neutral-800 p-3">
              <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                  <label class="mb-1 block text-sm font-medium">Pesquisar Artigo</label>
                  <Input v-model="linha.search" placeholder="Referência ou nome..." />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium">Artigo</label>
                  <Select
                    v-model="linha.artigo_id"
                    :options="filteredArtigos(linha.search).map(a => ({ value: a.id, label: `${a.referencia} - ${a.nome}` }))"
                  />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium">Fornecedor</label>
                  <Select
                    v-model="linha.fornecedor_id"
                    :options="[{ value: '', label: 'Sem fornecedor' }, ...fornecedores.map(f => ({ value: f.id, label: f.nome }))]"
                  />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium">Quantidade</label>
                  <Input v-model="linha.quantidade" type="number" min="0.01" step="0.01" />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium">Preço Unitário</label>
                  <Input v-model="linha.preco_unitario" type="number" min="0" step="0.01" />
                </div>
                <div class="flex items-end">
                  <p class="text-sm text-neutral-300">Subtotal: {{ lineSubtotal(linha).toFixed(2) }} €</p>
                </div>
              </div>
              <div class="mt-2 text-right">
                <Button size="sm" variant="destructive" type="button" @click="removeLine(idx)">Remover</Button>
              </div>
            </div>
          </div>

          <div class="text-right text-sm font-semibold">
            Total: {{ totalOrder.toFixed(2) }} €
          </div>
        </div>
      </FormModal>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
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
const fornecedores = ref([])
const artigos = ref([])
const modalOpen = ref(false)
const { pushToast } = useToast()

const today = () => new Date().toISOString().slice(0, 10)
const newLine = () => ({
  search: '',
  artigo_id: '',
  fornecedor_id: '',
  quantidade: 1,
  preco_unitario: ''
})

const form = ref({
  cliente_id: '',
  data_encomenda: today(),
  estado: 'rascunho',
  linhas: [newLine()]
})

const columns = [
  { header: 'Data', accessorKey: 'data_encomenda' },
  { header: 'Número', accessorKey: 'numero' },
  { header: 'Validade', accessorKey: 'validade' },
  { header: 'Cliente', accessorKey: 'cliente_nome' },
  { header: 'Valor Total', accessorKey: 'valor_total' },
  { header: 'Estado', accessorKey: 'estado' },
  { header: 'Ações', accessorKey: 'acoes' }
]

const totalOrder = computed(() => form.value.linhas.reduce((acc, linha) => acc + lineSubtotal(linha), 0))

const filteredArtigos = (search) => {
  const query = String(search || '').trim().toLowerCase()
  if (!query) return artigos.value
  return artigos.value.filter(a =>
    String(a.referencia || '').toLowerCase().includes(query) ||
    String(a.nome || '').toLowerCase().includes(query)
  )
}

const lineSubtotal = (linha) => Number(linha.quantidade || 0) * Number(linha.preco_unitario || 0)
const addLine = () => form.value.linhas.push(newLine())
const removeLine = (idx) => form.value.linhas.splice(idx, 1)

const resetForm = () => {
  form.value = {
    cliente_id: '',
    data_encomenda: today(),
    estado: 'rascunho',
    linhas: [newLine()]
  }
}

const openModal = async () => {
  await Promise.all([loadClientes(), loadFornecedores(), loadArtigos()])
  resetForm()
  modalOpen.value = true
}

const load = async () => {
  const res = await axios.get('/encomendas-clientes')
  rows.value = res.data.data
}

const loadClientes = async () => {
  const res = await axios.get('/entidades', { params: { tipo: 'cliente' } })
  clientes.value = res.data.data
}

const loadFornecedores = async () => {
  const res = await axios.get('/entidades', { params: { tipo: 'fornecedor' } })
  fornecedores.value = res.data.data
}

const loadArtigos = async () => {
  const res = await axios.get('/artigos', { params: { per_page: 200 } })
  artigos.value = res.data.data || []
}

const createOrder = async () => {
  try {
    const linhasValidas = form.value.linhas.filter(l => l.artigo_id).map(l => ({
      artigo_id: Number(l.artigo_id),
      fornecedor_id: l.fornecedor_id ? Number(l.fornecedor_id) : null,
      quantidade: Number(l.quantidade || 0),
      preco_unitario: Number(l.preco_unitario || 0)
    }))

    if (!form.value.cliente_id) {
      pushToast({ type: 'warning', title: 'Encomendas', message: 'Seleciona um cliente.' })
      return
    }
    if (linhasValidas.length === 0) {
      pushToast({ type: 'warning', title: 'Encomendas', message: 'Adiciona pelo menos uma linha com artigo.' })
      return
    }

    await axios.post('/encomendas-clientes', {
      cliente_id: Number(form.value.cliente_id),
      data_encomenda: form.value.data_encomenda || null,
      estado: form.value.estado || 'rascunho',
      linhas: linhasValidas
    })

    pushToast({ type: 'success', title: 'Encomendas', message: 'Encomenda de cliente criada.' })
    modalOpen.value = false
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: 'Encomendas', message: error?.response?.data?.message || 'Erro ao criar encomenda.' })
  }
}

const toSuppliers = async (id) => {
  try {
    await axios.post(`/encomendas-clientes/${id}/to-suppliers`)
    pushToast({ type: 'success', title: 'Encomendas', message: 'Encomendas de fornecedor geradas.' })
  } catch (error) {
    pushToast({ type: 'error', title: 'Encomendas', message: error?.response?.data?.message || 'Erro ao converter encomenda.' })
  }
}

const downloadPdf = (id) => window.open(`/api/v1/encomendas-clientes/${id}/pdf`, '_blank')

load()
</script>
