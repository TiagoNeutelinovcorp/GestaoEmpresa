<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">{{ title }}</h1>
        <Button variant="default" @click="modalOpen = true">Novo Registo</Button>
      </div>
      <DataTable :columns="columns" :data="rows">
        <template #estado="{ item }">
          {{ item.estado ? 'Ativo' : 'Inativo' }}
        </template>
        <template #acoes="{ item }">
          <Button size="sm" variant="destructive" @click.stop="removeItem(item)">Remover</Button>
        </template>
      </DataTable>

      <FormModal :is-open="modalOpen" title="Novo Registo" confirm-text="Guardar" @close="modalOpen = false" @confirm="saveItem">
        <div class="space-y-3">
          <Input v-model="form.nome" placeholder="Nome" />
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

const props = defineProps({
  title: { type: String, required: true },
  type: { type: String, required: true }
})

const rows = ref([])
const modalOpen = ref(false)
const form = ref({ nome: '', estado: true })
const { pushToast } = useToast()
const columns = [
  { header: 'Nome', accessorKey: 'nome' },
  { header: 'Estado', accessorKey: 'estado' },
  { header: 'Ações', accessorKey: 'acoes' }
]

const load = async () => {
  const res = await axios.get(`/lookups/${props.type}`)
  rows.value = res.data
}

const saveItem = async () => {
  try {
    await axios.post(`/lookups/${props.type}`, {
      nome: form.value.nome,
      estado: Boolean(form.value.estado)
    })
    pushToast({ type: 'success', title: props.title, message: 'Registo criado com sucesso.' })
    modalOpen.value = false
    form.value = { nome: '', estado: true }
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: props.title, message: error?.response?.data?.message || 'Erro ao criar registo.' })
  }
}

const removeItem = async (item) => {
  if (!window.confirm(`Remover "${item.nome}"?`)) return
  try {
    await axios.delete(`/lookups/${props.type}/${item.id}`)
    pushToast({ type: 'success', title: props.title, message: 'Registo removido com sucesso.' })
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: props.title, message: error?.response?.data?.message || 'Erro ao remover registo.' })
  }
}

load()
</script>
