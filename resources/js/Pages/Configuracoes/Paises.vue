<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Configurações - Entidades - Países</h1>
        <Button variant="default" @click="modalOpen = true">Novo País</Button>
      </div>
      <DataTable :columns="columns" :data="rows">
        <template #acoes="{ item }">
          <div class="flex gap-2">
            <Button size="sm" variant="outline" @click.stop="editPais(item)">Editar</Button>
            <Button size="sm" variant="destructive" @click.stop="removePais(item.id)">Remover</Button>
          </div>
        </template>
      </DataTable>

      <FormModal :is-open="modalOpen" :title="editingId ? 'Editar País' : 'Novo País'" confirm-text="Guardar" @close="closeModal" @confirm="savePais">
        <div class="space-y-3">
          <Input v-model="form.nome" placeholder="Nome" />
          <Input v-model="form.sigla" placeholder="Sigla (2 letras)" maxlength="2" />
          <Input v-model="form.codigo_iso3" placeholder="Código ISO3" maxlength="3" />
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
import axios from '@/axios'
import { useToast } from '@/composables/useToast'
const rows = ref([])
const modalOpen = ref(false)
const editingId = ref(null)
const form = ref({ nome: '', sigla: '', codigo_iso3: '' })
const { pushToast } = useToast()
const columns = [
  { header: 'Nome', accessorKey: 'nome' },
  { header: 'Sigla', accessorKey: 'sigla' },
  { header: 'ISO3', accessorKey: 'codigo_iso3' },
  { header: 'Ações', accessorKey: 'acoes' }
]

const load = async () => {
  const res = await axios.get('/paises')
  rows.value = res.data
}

const savePais = async () => {
  try {
    const payload = {
      nome: form.value.nome,
      sigla: form.value.sigla.toUpperCase(),
      codigo_iso3: form.value.codigo_iso3.toUpperCase()
    }

    if (editingId.value) {
      await axios.put(`/paises/${editingId.value}`, payload)
      pushToast({ type: 'success', title: 'Países', message: 'País atualizado com sucesso.' })
    } else {
      await axios.post('/paises', payload)
      pushToast({ type: 'success', title: 'Países', message: 'País criado com sucesso.' })
    }

    closeModal()
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: 'Países', message: error?.response?.data?.message || 'Erro ao guardar país.' })
  }
}

const editPais = (item) => {
  editingId.value = item.id
  form.value = {
    nome: item.nome || '',
    sigla: item.sigla || '',
    codigo_iso3: item.codigo_iso3 || ''
  }
  modalOpen.value = true
}

const removePais = async (id) => {
  if (!window.confirm('Remover este país?')) return
  try {
    await axios.delete(`/paises/${id}`)
    pushToast({ type: 'success', title: 'Países', message: 'País removido com sucesso.' })
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: 'Países', message: error?.response?.data?.message || 'Erro ao remover país.' })
  }
}

const closeModal = () => {
  modalOpen.value = false
  editingId.value = null
  form.value = { nome: '', sigla: '', codigo_iso3: '' }
}

load()
</script>
