<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Ordens de Trabalho</h1>
        <Button variant="default" @click="open = true">Nova Ordem</Button>
      </div>

      <DataTable :columns="columns" :data="rows" />

      <FormModal :is-open="open" title="Nova Ordem de Trabalho" confirm-text="Guardar" @close="open = false" @confirm="save">
        <div class="space-y-3">
          <div>
            <label class="mb-1 block text-sm font-medium">Título</label>
            <Input v-model="form.titulo" placeholder="Ex: Manutenção cliente X" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium">Descrição</label>
            <textarea v-model="form.descricao" rows="4" class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium">Estado</label>
            <Select v-model="form.estado" :options="estadoOptions" />
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

const open = ref(false)
const rows = ref([])
const form = ref({ titulo: '', descricao: '', estado: 'rascunho' })
const estadoOptions = [
  { value: 'rascunho', label: 'Rascunho' },
  { value: 'em_execucao', label: 'Em execução' },
  { value: 'fechada', label: 'Fechada' }
]

const columns = [
  { header: 'Número', accessorKey: 'numero' },
  { header: 'Título', accessorKey: 'titulo' },
  { header: 'Estado', accessorKey: 'estado' },
  { header: 'Criada em', accessorKey: 'created_at' }
]

const save = () => {
  const id = rows.value.length + 1
  rows.value.unshift({
    id,
    numero: `OT-${String(id).padStart(6, '0')}`,
    titulo: form.value.titulo,
    estado: form.value.estado,
    created_at: new Date().toISOString().slice(0, 10)
  })
  form.value = { titulo: '', descricao: '', estado: 'rascunho' }
  open.value = false
}
</script>
