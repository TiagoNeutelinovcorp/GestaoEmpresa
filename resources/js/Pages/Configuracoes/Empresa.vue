<template>
  <MainLayout>
    <div class="max-w-2xl space-y-4">
      <h1 class="text-2xl font-bold">Configurações - Empresa</h1>
      <div class="rounded border bg-white p-4 space-y-3">
        <Input v-model="form.nome" placeholder="Nome" />
        <Input v-model="form.numero_contribuinte" placeholder="Número Contribuinte" />
        <Input v-model="form.morada" placeholder="Morada" />
        <Input v-model="form.codigo_postal" placeholder="Código Postal" />
        <Input v-model="form.localidade" placeholder="Localidade" />
        <Input v-model="form.logo_path" placeholder="Logo path" />
        <Button variant="default" @click="save">Guardar</Button>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { reactive } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import Input from '@/Components/ui/Input.vue'
import Button from '@/Components/ui/Button.vue'
import axios from '@/axios'
import { useToast } from '@/composables/useToast'

const form = reactive({
  nome: '', numero_contribuinte: '', morada: '', codigo_postal: '', localidade: '', logo_path: ''
})
const { pushToast } = useToast()

const load = async () => {
  try {
    const res = await axios.get('/settings/company')
    if (res.data) Object.assign(form, res.data)
  } catch (error) {
    pushToast({ type: 'error', title: 'Empresa', message: error?.response?.data?.message || 'Erro ao carregar dados da empresa.' })
  }
}

const save = async () => {
  try {
    await axios.put('/settings/company', form)
    pushToast({ type: 'success', title: 'Empresa', message: 'Dados da empresa guardados.' })
  } catch (error) {
    pushToast({ type: 'error', title: 'Empresa', message: error?.response?.data?.message || 'Erro ao guardar dados da empresa.' })
  }
}

load()
</script>
