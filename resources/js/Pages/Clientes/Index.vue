<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Clientes</h1>
        <Button variant="default" @click="openCreateModal">
          + Novo Cliente
        </Button>
      </div>

      <DataTable
        :columns="columns"
        :data="clientes"
        @row-click="handleRowClick"
      >
        <template #telefone="{ item }">
          {{ item.telefone || '-' }}
        </template>
        <template #telemovel="{ item }">
          {{ item.telemovel || '-' }}
        </template>
        <template #estado="{ item }">
          <span :class="item.estado ? 'text-green-600' : 'text-red-600'">
            {{ item.estado ? 'Ativo' : 'Inativo' }}
          </span>
        </template>
      </DataTable>
    </div>

    <!-- Modal Form -->
    <FormModal
      :is-open="modalOpen"
      title="Cliente"
      confirm-text="Salvar"
      @close="modalOpen = false"
      @confirm="salvarCliente"
    >
      <form @submit.prevent="salvarCliente" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">NIF</label>
          <Input v-model="form.nif" required />
          <button
            type="button"
            @click="consultarNif"
            class="mt-1 text-sm text-neutral-300 underline hover:text-neutral-100"
          >
            Consultar VIES
          </button>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Nome</label>
          <Input v-model="form.nome" required />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Morada</label>
          <Input v-model="form.morada" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Código Postal</label>
            <Input v-model="form.codigo_postal" placeholder="1234-567" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Localidade</label>
            <Input v-model="form.localidade" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">País</label>
          <Select
            v-model="form.pais_id"
            :options="paises.map(p => ({ value: p.id, label: p.nome }))"
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Telefone</label>
            <Input v-model="form.telefone" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Telemóvel</label>
            <Input v-model="form.telemovel" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <Input v-model="form.email" type="email" />
        </div>

        <div>
          <label class="flex items-center gap-2">
            <input type="checkbox" v-model="form.consentimento_rgpd" />
            <span class="text-sm">Consentimento RGPD</span>
          </label>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Observações</label>
          <textarea
            v-model="form.observacoes"
            rows="3"
            class="flex w-full rounded-md border border-neutral-700 bg-neutral-900 px-3 py-2 text-sm text-neutral-100 placeholder:text-neutral-400"
          ></textarea>
        </div>
      </form>
    </FormModal>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import DataTable from '@/Components/ui/DataTable.vue'
import FormModal from '@/Components/ui/FormModal.vue'
import Input from '@/Components/ui/Input.vue'
import Select from '@/Components/ui/Select.vue'
import Button from '@/Components/ui/Button.vue'
import axios from '@/axios'
import { useToast } from '@/composables/useToast'

const columns = [
  { header: 'NIF', accessorKey: 'nif', sortable: true },
  { header: 'Nome', accessorKey: 'nome', sortable: true },
  { header: 'Telefone', accessorKey: 'telefone', sortable: false },
  { header: 'Telemóvel', accessorKey: 'telemovel', sortable: false },
  { header: 'Email', accessorKey: 'email', sortable: true },
  { header: 'Estado', accessorKey: 'estado', sortable: true }
]

const clientes = ref([])
const paises = ref([])
const modalOpen = ref(false)
const form = ref({
  tipo: 'cliente',
  nif: '',
  nome: '',
  morada: '',
  codigo_postal: '',
  localidade: '',
  pais_id: '',
  telefone: '',
  telemovel: '',
  email: '',
  consentimento_rgpd: false,
  observacoes: '',
  estado: true
})
const { pushToast } = useToast()

const carregarClientes = async () => {
  const response = await axios.get('/entidades', { params: { tipo: 'cliente' } })
  clientes.value = response.data.data
}

const carregarPaises = async () => {
  const response = await axios.get('/paises')
  paises.value = response.data
  if (!form.value.pais_id) {
    const portugal = paises.value.find((p) => p.sigla === 'PT')
    form.value.pais_id = portugal ? String(portugal.id) : ''
  }
}

const consultarNif = async () => {
  if (!form.value.nif) return

  try {
    const response = await axios.post('/entidades/consultar-nif', {
      nif: form.value.nif,
      pais_sigla: 'PT'
    })

    if (response.data.success) {
      form.value.nome = response.data.nome || form.value.nome
      pushToast({
        type: 'success',
        title: 'VIES',
        message: 'Dados obtidos com sucesso.'
      })
    }
  } catch (error) {
    pushToast({
      type: 'error',
      title: 'VIES',
      message: 'NIF não encontrado no VIES.'
    })
  }
}

const salvarCliente = async () => {
  try {
    await axios.post('/entidades', {
      ...form.value,
      pais_id: form.value.pais_id ? Number(form.value.pais_id) : null
    })
    pushToast({
      type: 'success',
      title: 'Cliente',
      message: 'Cliente salvo com sucesso.'
    })
    modalOpen.value = false
    form.value = {
      tipo: 'cliente',
      nif: '',
      nome: '',
      morada: '',
      codigo_postal: '',
      localidade: '',
      pais_id: '',
      telefone: '',
      telemovel: '',
      email: '',
      consentimento_rgpd: false,
      observacoes: '',
      estado: true
    }
    const portugal = paises.value.find((p) => p.sigla === 'PT')
    form.value.pais_id = portugal ? String(portugal.id) : ''
    carregarClientes()
  } catch (error) {
    const message = error?.response?.data?.message
    const detail = error?.response?.data?.error
    const validationErrors = error?.response?.data?.errors
      ? Object.values(error.response.data.errors).flat().join('\n')
      : null
    pushToast({
      type: 'error',
      title: 'Cliente',
      message: validationErrors || message || detail || 'Erro ao salvar cliente.'
    })
  }
}

const openCreateModal = () => {
  modalOpen.value = true
}

const handleRowClick = (cliente) => {
  console.log('Cliente clicado:', cliente)
}

onMounted(() => {
  carregarClientes()
  carregarPaises()
})
</script>
