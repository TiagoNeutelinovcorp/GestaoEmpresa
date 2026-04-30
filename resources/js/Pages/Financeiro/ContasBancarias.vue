<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Financeiro - Contas Bancárias</h1>
        <Button variant="default" @click="modalOpen = true">Nova Conta Bancária</Button>
      </div>
      <DataTable :columns="columns" :data="rows" />

      <FormModal :is-open="modalOpen" title="Nova Conta Bancária" confirm-text="Guardar" @close="modalOpen = false" @confirm="save">
        <div class="space-y-3">
          <Input v-model="form.banco" placeholder="Banco" />
          <Input v-model="form.iban" placeholder="IBAN" />
          <Input v-model="form.swift" placeholder="SWIFT" />
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
const form = ref({ banco: '', iban: '', swift: '' })
const { pushToast } = useToast()
const columns = [
  { header: 'Banco', accessorKey: 'banco' },
  { header: 'IBAN', accessorKey: 'iban' },
  { header: 'SWIFT', accessorKey: 'swift' },
  { header: 'Estado', accessorKey: 'estado' }
]
const load = async () => {
  const res = await axios.get('/contas-bancarias')
  rows.value = res.data.data
}

const save = async () => {
  try {
    await axios.post('/contas-bancarias', { ...form.value, estado: true })
    pushToast({ type: 'success', title: 'Contas Bancárias', message: 'Conta criada com sucesso.' })
    modalOpen.value = false
    form.value = { banco: '', iban: '', swift: '' }
    await load()
  } catch (error) {
    pushToast({ type: 'error', title: 'Contas Bancárias', message: error?.response?.data?.message || 'Erro ao criar conta.' })
  }
}

load()
</script>
