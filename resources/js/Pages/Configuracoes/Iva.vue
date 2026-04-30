<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Configurações - Financeiro - IVA</h1>
        <Button variant="default" @click="modalOpen = true">Novo IVA</Button>
      </div>
      <DataTable :columns="columns" :data="rows">
        <template #estado="{ item }">{{ item.estado ? 'Ativo' : 'Inativo' }}</template>
        <template #acoes="{ item }">
          <div class="flex gap-2">
            <Button size="sm" variant="outline" @click.stop="editIva(item)">Editar</Button>
            <Button size="sm" variant="destructive" @click.stop="removeIva(item.id)">Remover</Button>
          </div>
        </template>
      </DataTable>

      <FormModal :is-open="modalOpen" :title="editingId ? 'Editar IVA' : 'Novo IVA'" confirm-text="Guardar" @close="closeModal" @confirm="saveIva">
        <div class="space-y-3">
          <Input v-model="form.nome" placeholder="Nome" />
          <Input v-model="form.percentagem" type="number" min="0" max="100" placeholder="Percentagem" />
          <Select
            v-model="form.estado"
            :options="[
              { value: true, label: 'Ativo' },
              { value: false, label: 'Inativo' }
            ]"
          />
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
const modalOpen = ref(false)
const editingId = ref(null)
const form = ref({ nome: '', percentagem: '', estado: true })
const { pushToast } = useToast()
const columns = [
  { header: 'Nome', accessorKey: 'nome' },
  { header: 'Percentagem', accessorKey: 'percentagem' },
  { header: 'Estado', accessorKey: 'estado' },
  { header: 'Ações', accessorKey: 'acoes' }
]
const load = async () => {
  const res = await axios.get('/ivas')
  rows.value = res.data
}

const saveIva = async () => {
  try {
    const payload = {
      nome: form.value.nome,
      percentagem: Number(form.value.percentagem),
      estado: Boolean(form.value.estado)
    }

    if (editingId.value) {
      await axios.put(`/ivas/${editingId.value}`, payload)
      pushToast({ type: 'success', title: 'IVA', message: 'IVA atualizado com sucesso.' })
    } else {
      await axios.post('/ivas', payload)
      pushToast({ type: 'success', title: 'IVA', message: 'IVA criado com sucesso.' })
    }

    closeModal()
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: 'IVA', message: error?.response?.data?.message || 'Erro ao guardar IVA.' })
  }
}

const editIva = (item) => {
  editingId.value = item.id
  form.value = {
    nome: item.nome || '',
    percentagem: item.percentagem ?? '',
    estado: Boolean(item.estado)
  }
  modalOpen.value = true
}

const removeIva = async (id) => {
  if (!window.confirm('Remover este IVA?')) return
  try {
    await axios.delete(`/ivas/${id}`)
    pushToast({ type: 'success', title: 'IVA', message: 'IVA removido com sucesso.' })
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: 'IVA', message: error?.response?.data?.message || 'Erro ao remover IVA.' })
  }
}

const closeModal = () => {
  modalOpen.value = false
  editingId.value = null
  form.value = { nome: '', percentagem: '', estado: true }
}

load()
</script>
