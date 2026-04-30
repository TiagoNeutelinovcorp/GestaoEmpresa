<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Propostas</h1>
        <Button variant="default" @click="openModal">Nova Proposta</Button>
      </div>

      <DataTable :columns="columns" :data="propostas">
        <template #valor_total="{ item }">{{ Number(item.valor_total).toFixed(2) }} €</template>
        <template #acoes="{ item }">
          <div class="flex gap-2">
            <Button size="sm" variant="outline" @click.stop="downloadPdf(item.id)">PDF</Button>
            <Button size="sm" variant="secondary" @click.stop="convert(item.id)">Converter</Button>
          </div>
        </template>
      </DataTable>

      <FormModal
        :is-open="modalOpen"
        title="Nova Proposta"
        confirm-text="Guardar"
        @close="modalOpen = false"
        @confirm="createProposta"
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
              <label class="mb-1 block text-sm font-medium">Data da Proposta</label>
              <Input v-model="form.data_proposta" type="date" @change="setDefaultValidade" />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium">Validade</label>
              <Input v-model="form.validade" type="date" />
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
                <div>
                  <label class="mb-1 block text-sm font-medium">Preço de Custo</label>
                  <Input v-model="linha.preco_custo" type="number" min="0" step="0.01" />
                </div>
              </div>
              <div class="mt-2 flex items-center justify-between">
                <p class="text-sm text-neutral-300">Subtotal: {{ lineSubtotal(linha).toFixed(2) }} €</p>
                <Button size="sm" variant="destructive" type="button" @click="removeLine(idx)">Remover</Button>
              </div>
            </div>
          </div>

          <div class="text-right text-sm font-semibold">
            Total: {{ totalProposta.toFixed(2) }} €
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

const propostas = ref([])
const clientes = ref([])
const fornecedores = ref([])
const artigos = ref([])
const modalOpen = ref(false)
const { pushToast } = useToast()

const today = () => new Date().toISOString().slice(0, 10)
const plusDays = (dateString, days) => {
  const d = new Date(dateString)
  d.setDate(d.getDate() + days)
  return d.toISOString().slice(0, 10)
}

const newLine = () => ({
  search: '',
  artigo_id: '',
  fornecedor_id: '',
  quantidade: 1,
  preco_unitario: '',
  preco_custo: ''
})

const form = ref({
  cliente_id: '',
  data_proposta: today(),
  validade: plusDays(today(), 30),
  estado: 'rascunho',
  linhas: [newLine()]
})

const columns = [
  { header: 'Data', accessorKey: 'data_proposta' },
  { header: 'Número', accessorKey: 'numero' },
  { header: 'Validade', accessorKey: 'validade' },
  { header: 'Cliente', accessorKey: 'cliente_nome' },
  { header: 'Valor Total', accessorKey: 'valor_total' },
  { header: 'Estado', accessorKey: 'estado' },
  { header: 'Ações', accessorKey: 'acoes' }
]

const totalProposta = computed(() => form.value.linhas.reduce((acc, linha) => acc + lineSubtotal(linha), 0))

const filteredArtigos = (search) => {
  const query = String(search || '').trim().toLowerCase()
  if (!query) return artigos.value
  return artigos.value.filter(a =>
    String(a.referencia || '').toLowerCase().includes(query) ||
    String(a.nome || '').toLowerCase().includes(query)
  )
}

const lineSubtotal = (linha) => {
  const quantidade = Number(linha.quantidade || 0)
  const preco = Number(linha.preco_unitario || 0)
  return quantidade * preco
}

const setDefaultValidade = () => {
  if (!form.value.data_proposta) return
  form.value.validade = plusDays(form.value.data_proposta, 30)
}

const addLine = () => form.value.linhas.push(newLine())
const removeLine = (idx) => form.value.linhas.splice(idx, 1)

const resetForm = () => {
  form.value = {
    cliente_id: '',
    data_proposta: today(),
    validade: plusDays(today(), 30),
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
  const res = await axios.get('/propostas')
  propostas.value = res.data.data
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

const createProposta = async () => {
  try {
    const linhasValidas = form.value.linhas.filter(l => l.artigo_id).map(l => ({
      artigo_id: Number(l.artigo_id),
      fornecedor_id: l.fornecedor_id ? Number(l.fornecedor_id) : null,
      quantidade: Number(l.quantidade || 0),
      preco_unitario: Number(l.preco_unitario || 0),
      preco_custo: Number(l.preco_custo || 0)
    }))

    if (!form.value.cliente_id) {
      pushToast({ type: 'warning', title: 'Propostas', message: 'Seleciona um cliente.' })
      return
    }
    if (linhasValidas.length === 0) {
      pushToast({ type: 'warning', title: 'Propostas', message: 'Adiciona pelo menos uma linha com artigo.' })
      return
    }

    await axios.post('/propostas', {
      cliente_id: Number(form.value.cliente_id),
      data_proposta: form.value.data_proposta || null,
      validade: form.value.validade || null,
      estado: form.value.estado || 'rascunho',
      linhas: linhasValidas
    })

    pushToast({ type: 'success', title: 'Propostas', message: 'Proposta criada com sucesso.' })
    modalOpen.value = false
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: 'Propostas', message: error?.response?.data?.message || 'Erro ao criar proposta.' })
  }
}

const convert = async (id) => {
  await axios.post(`/propostas/${id}/to-order`)
  pushToast({ type: 'success', title: 'Propostas', message: 'Convertida para encomenda cliente.' })
}

const downloadPdf = (id) => window.open(`/api/v1/propostas/${id}/pdf`, '_blank')

load()
</script>
