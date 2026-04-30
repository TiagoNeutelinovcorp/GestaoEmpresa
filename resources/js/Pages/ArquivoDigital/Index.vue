<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Arquivo Digital</h1>
        <Button variant="default" @click="modalOpen = true">Novo Documento</Button>
      </div>
      <DataTable :columns="columns" :data="rows">
        <template #download="{ item }">
          <a class="text-neutral-200 underline hover:text-white" :href="`/api/v1/arquivo-digital/${item.id}/download`" target="_blank">Download</a>
        </template>
      </DataTable>

      <FormModal
        :is-open="modalOpen"
        title="Novo Documento"
        confirm-text="Guardar"
        @close="closeModal"
        @confirm="saveDocument"
      >
        <div class="space-y-4">
          <div>
            <label class="mb-1 block text-sm font-medium">Nome</label>
            <Input v-model="form.nome" placeholder="Nome do documento" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium">Entidade (opcional)</label>
            <Select v-model="form.entidade_id" :options="entidades.map(e => ({ value: e.id, label: e.nome }))" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium">Ficheiro</label>
            <input
              type="file"
              class="flex h-10 w-full rounded-md border border-neutral-700 bg-neutral-900 px-3 py-2 text-sm text-neutral-100"
              @change="onFileChange"
            >
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
import Input from '@/Components/ui/Input.vue'
import Select from '@/Components/ui/Select.vue'
import axios from '@/axios'
import { useToast } from '@/composables/useToast'
const rows = ref([])
const entidades = ref([])
const modalOpen = ref(false)
const selectedFile = ref(null)
const form = ref({
  nome: '',
  entidade_id: ''
})
const { pushToast } = useToast()
const columns = [
  { header: 'Nome', accessorKey: 'nome' },
  { header: 'Entidade', accessorKey: 'entidade_nome' },
  { header: 'Tipo', accessorKey: 'mime_type' },
  { header: 'Tamanho', accessorKey: 'size' },
  { header: 'Download', accessorKey: 'download' }
]
axios.get('/arquivo-digital').then((res) => { rows.value = res.data.data })

const load = async () => {
  const res = await axios.get('/arquivo-digital')
  rows.value = res.data.data
}

const loadEntidades = async () => {
  const res = await axios.get('/entidades')
  entidades.value = res.data.data || []
}

const onFileChange = (event) => {
  selectedFile.value = event.target.files?.[0] || null
}

const closeModal = () => {
  modalOpen.value = false
  form.value = { nome: '', entidade_id: '' }
  selectedFile.value = null
}

const saveDocument = async () => {
  if (!selectedFile.value) {
    pushToast({ type: 'warning', title: 'Arquivo Digital', message: 'Seleciona um ficheiro.' })
    return
  }

  try {
    const payload = new FormData()
    payload.append('nome', form.value.nome || selectedFile.value.name)
    payload.append('ficheiro', selectedFile.value)
    if (form.value.entidade_id) {
      payload.append('entidade_id', String(form.value.entidade_id))
    }

    await axios.post('/arquivo-digital', payload, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    pushToast({ type: 'success', title: 'Arquivo Digital', message: 'Documento adicionado com sucesso.' })
    closeModal()
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: 'Arquivo Digital', message: error?.response?.data?.message || 'Erro ao adicionar documento.' })
  }
}

load()
loadEntidades()
</script>
