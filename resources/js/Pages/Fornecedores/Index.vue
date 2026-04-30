<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Fornecedores</h1>
        <Button variant="default" @click="openCreateModal">
          + Novo Fornecedor
        </Button>
      </div>

      <DataTable
        :columns="columns"
        :data="fornecedores"
        @row-click="handleRowClick"
      >
        <template #telefone="{ item }">
          {{ item.telefone || '-' }}
        </template>
        <template #telemovel="{ item }">
          {{ item.telemovel || '-' }}
        </template>
      </DataTable>
    </div>

    <FormModal
      :is-open="modalOpen"
      title="Fornecedor"
      confirm-text="Salvar"
      @close="modalOpen = false"
      @confirm="salvarFornecedor"
    >
      <form @submit.prevent="salvarFornecedor" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">NIF</label>
          <Input v-model="form.nif" required />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Nome</label>
          <Input v-model="form.nome" required />
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
          <label class="block text-sm font-medium mb-1">País</label>
          <Select
            v-model="form.pais_id"
            :options="paises.map(p => ({ value: p.id, label: p.nome }))"
          />
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
  { header: 'Email', accessorKey: 'email', sortable: true }
]

const fornecedores = ref([])
const paises = ref([])
const modalOpen = ref(false)
const form = ref({
  tipo: 'fornecedor',
  nif: '',
  nome: '',
  pais_id: '',
  telefone: '',
  telemovel: '',
  email: '',
  consentimento_rgpd: false,
  estado: true
})
const { pushToast } = useToast()

const carregarFornecedores = async () => {
  const response = await axios.get('/entidades', { params: { tipo: 'fornecedor' } })
  fornecedores.value = response.data.data
}

const carregarPaises = async () => {
  const response = await axios.get('/paises')
  paises.value = response.data
  if (!form.value.pais_id) {
    const portugal = paises.value.find((p) => p.sigla === 'PT')
    form.value.pais_id = portugal ? String(portugal.id) : ''
  }
}

const openCreateModal = () => {
  modalOpen.value = true
}

const salvarFornecedor = async () => {
  try {
    await axios.post('/entidades', form.value)
    modalOpen.value = false
    form.value = {
      tipo: 'fornecedor',
      nif: '',
      nome: '',
      pais_id: '',
      telefone: '',
      telemovel: '',
      email: '',
      consentimento_rgpd: false,
      estado: true
    }
    const portugal = paises.value.find((p) => p.sigla === 'PT')
    form.value.pais_id = portugal ? String(portugal.id) : ''
    await carregarFornecedores()
    pushToast({
      type: 'success',
      title: 'Fornecedor',
      message: 'Fornecedor salvo com sucesso.'
    })
  } catch (error) {
    const message = error?.response?.data?.message || 'Erro ao salvar fornecedor.'
    pushToast({
      type: 'error',
      title: 'Fornecedor',
      message
    })
  }
}

const handleRowClick = (fornecedor) => {
  console.log('Fornecedor clicado:', fornecedor)
}

onMounted(() => {
  carregarFornecedores()
  carregarPaises()
})
</script>
