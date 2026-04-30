<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Configurações - Artigos</h1>
        <Button variant="default" @click="modalOpen = true">Novo Artigo</Button>
      </div>
      <DataTable :columns="columns" :data="rows">
        <template #preco="{ item }">{{ Number(item.preco || 0).toFixed(2) }} €</template>
        <template #acoes="{ item }">
          <div class="flex gap-2">
            <Button size="sm" variant="outline" @click.stop="editArtigo(item)">Editar</Button>
            <Button size="sm" variant="destructive" @click.stop="removeArtigo(item.id)">Remover</Button>
          </div>
        </template>
      </DataTable>

      <FormModal :is-open="modalOpen" :title="editingId ? 'Editar Artigo' : 'Novo Artigo'" confirm-text="Guardar" @close="closeModal" @confirm="saveArtigo">
        <div class="space-y-3">
          <Input v-model="form.referencia" placeholder="Referência" />
          <Input v-model="form.nome" placeholder="Nome" />
          <Input v-model="form.descricao" placeholder="Descrição" />
          <Input v-model="form.preco" type="number" min="0" placeholder="Preço" />
          <Select v-model="form.iva_id" :options="ivas.map(i => ({ value: i.id, label: `${i.nome} (${i.percentagem}%)` }))" />
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
const ivas = ref([])
const modalOpen = ref(false)
const editingId = ref(null)
const form = ref({ referencia: '', nome: '', descricao: '', preco: '', iva_id: '' })
const { pushToast } = useToast()
const columns = [
  { header: 'Referência', accessorKey: 'referencia' },
  { header: 'Foto', accessorKey: 'foto_path' },
  { header: 'Nome', accessorKey: 'nome' },
  { header: 'Descrição', accessorKey: 'descricao' },
  { header: 'Preço', accessorKey: 'preco' },
  { header: 'Ações', accessorKey: 'acoes' }
]
const load = async () => {
  const res = await axios.get('/artigos')
  rows.value = res.data.data
}

const loadIvas = async () => {
  const res = await axios.get('/ivas')
  ivas.value = res.data
}

const saveArtigo = async () => {
  try {
    const payload = {
      referencia: form.value.referencia,
      nome: form.value.nome,
      descricao: form.value.descricao,
      preco: Number(form.value.preco || 0),
      iva_id: form.value.iva_id ? Number(form.value.iva_id) : null,
      estado: true
    }

    if (editingId.value) {
      await axios.put(`/artigos/${editingId.value}`, payload)
      pushToast({ type: 'success', title: 'Artigos', message: 'Artigo atualizado com sucesso.' })
    } else {
      await axios.post('/artigos', payload)
      pushToast({ type: 'success', title: 'Artigos', message: 'Artigo criado com sucesso.' })
    }

    closeModal()
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: 'Artigos', message: error?.response?.data?.message || 'Erro ao guardar artigo.' })
  }
}

const editArtigo = (item) => {
  editingId.value = item.id
  form.value = {
    referencia: item.referencia || '',
    nome: item.nome || '',
    descricao: item.descricao || '',
    preco: item.preco ?? '',
    iva_id: item.iva_id || ''
  }
  modalOpen.value = true
}

const removeArtigo = async (id) => {
  if (!window.confirm('Remover este artigo?')) return
  try {
    await axios.delete(`/artigos/${id}`)
    pushToast({ type: 'success', title: 'Artigos', message: 'Artigo removido com sucesso.' })
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: 'Artigos', message: error?.response?.data?.message || 'Erro ao remover artigo.' })
  }
}

const closeModal = () => {
  modalOpen.value = false
  editingId.value = null
  form.value = { referencia: '', nome: '', descricao: '', preco: '', iva_id: '' }
}

load()
loadIvas()
</script>
