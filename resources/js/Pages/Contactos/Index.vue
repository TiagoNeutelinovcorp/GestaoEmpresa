<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Contactos</h1>
        <Button variant="default" @click="modalOpen = true">Novo Contacto</Button>
      </div>
      <DataTable :columns="columns" :data="rows" />

      <FormModal
        :is-open="modalOpen"
        title="Novo Contacto"
        confirm-text="Guardar"
        @close="modalOpen = false"
        @confirm="saveContacto"
      >
        <form @submit.prevent="saveContacto" class="space-y-4">
          <div>
            <label class="mb-1 block text-sm font-medium">Entidade</label>
            <Select
              v-model="form.entidade_id"
              :options="entidades.map(e => ({ value: e.id, label: e.nome }))"
            />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="mb-1 block text-sm font-medium">Nome</label>
              <Input v-model="form.nome" />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium">Apelido</label>
              <Input v-model="form.apelido" />
            </div>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium">Função</label>
            <Select
              v-model="form.funcao_id"
              :options="funcoes.map(f => ({ value: f.id, label: f.nome }))"
            />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="mb-1 block text-sm font-medium">Telefone</label>
              <Input v-model="form.telefone" />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium">Telemóvel</label>
              <Input v-model="form.telemovel" />
            </div>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium">Email</label>
            <Input v-model="form.email" type="email" />
          </div>
        </form>
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
const entidades = ref([])
const funcoes = ref([])
const modalOpen = ref(false)
const { pushToast } = useToast()

const form = ref({
  entidade_id: '',
  nome: '',
  apelido: '',
  funcao_id: '',
  telefone: '',
  telemovel: '',
  email: '',
  consentimento_rgpd: false,
  estado: true
})

const columns = [
  { header: 'Nome', accessorKey: 'nome' },
  { header: 'Apelido', accessorKey: 'apelido' },
  { header: 'Função', accessorKey: 'funcao_nome' },
  { header: 'Entidade', accessorKey: 'entidade_nome' },
  { header: 'Telefone', accessorKey: 'telefone' },
  { header: 'Telemóvel', accessorKey: 'telemovel' },
  { header: 'Email', accessorKey: 'email' }
]

const load = async () => {
  const res = await axios.get('/contactos')
  rows.value = res.data.data.map((c) => ({
    ...c,
    entidade_nome: c.entidade?.nome,
    funcao_nome: c.funcao?.nome
  }))
}

const loadSelects = async () => {
  const [entidadesRes, funcoesRes] = await Promise.all([
    axios.get('/entidades'),
    axios.get('/lookups/funcoes-contacto')
  ])
  entidades.value = entidadesRes.data.data || []
  funcoes.value = funcoesRes.data || []
}

const saveContacto = async () => {
  try {
    await axios.post('/contactos', {
      ...form.value,
      entidade_id: form.value.entidade_id ? Number(form.value.entidade_id) : null,
      funcao_id: form.value.funcao_id ? Number(form.value.funcao_id) : null
    })
    pushToast({ type: 'success', title: 'Contactos', message: 'Contacto criado com sucesso.' })
    modalOpen.value = false
    form.value = {
      entidade_id: '',
      nome: '',
      apelido: '',
      funcao_id: '',
      telefone: '',
      telemovel: '',
      email: '',
      consentimento_rgpd: false,
      estado: true
    }
    await load()
  } catch (error) {
    const message = error?.response?.data?.message || 'Erro ao criar contacto.'
    pushToast({ type: 'error', title: 'Contactos', message })
  }
}

load()
loadSelects()
</script>
