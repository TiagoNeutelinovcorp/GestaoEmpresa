<template>
  <MainLayout>
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Calendário</h1>
        <Button variant="default" @click="modalOpen = true">Agendar Atividade</Button>
      </div>

      <div class="grid grid-cols-1 gap-4 rounded-md border border-neutral-800 bg-black p-4 md:grid-cols-3">
        <div>
          <label class="mb-1 block text-sm font-medium text-neutral-300">Utilizador</label>
          <Input v-model="filters.user_id" placeholder="ID utilizador" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-neutral-300">Entidade</label>
          <Input v-model="filters.entidade_id" placeholder="ID entidade" />
        </div>
        <div class="flex items-end">
          <Button variant="secondary" @click="loadEvents">Filtrar</Button>
        </div>
      </div>

      <div class="calendar-dark rounded-md border border-neutral-800 bg-black p-4">
        <FullCalendar :options="calendarOptions" />
      </div>

      <FormModal
        :is-open="modalOpen"
        title="Agendar Atividade"
        confirm-text="Guardar"
        @close="modalOpen = false"
        @confirm="saveActivity"
      >
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="mb-1 block text-sm font-medium">Data</label>
              <Input v-model="activity.data" type="date" />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium">Hora</label>
              <Input v-model="activity.hora" type="time" />
            </div>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium">Duração (minutos)</label>
            <Input v-model="activity.duracao_minutos" type="number" min="1" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="mb-1 block text-sm font-medium">Utilizador</label>
              <Select v-model="activity.user_id" :options="users.map(u => ({ value: u.id, label: u.name }))" />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium">Entidade</label>
              <Select v-model="activity.entidade_id" :options="entidades.map(e => ({ value: e.id, label: e.nome }))" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="mb-1 block text-sm font-medium">Tipo</label>
              <Select v-model="activity.tipo_id" :options="tipos.map(t => ({ value: t.id, label: t.nome }))" />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium">Ação</label>
              <Select v-model="activity.acao_id" :options="acoes.map(a => ({ value: a.id, label: a.nome }))" />
            </div>
          </div>
          <div class="grid grid-cols-3 gap-4 text-sm">
            <label class="flex items-center gap-2"><input v-model="activity.partilha" type="checkbox"> Partilha</label>
            <label class="flex items-center gap-2"><input v-model="activity.conhecimento" type="checkbox"> Conhecimento</label>
            <label class="flex items-center gap-2"><input v-model="activity.estado" type="checkbox"> Ativo</label>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium">Descrição</label>
            <textarea v-model="activity.descricao" rows="3" class="flex w-full rounded-md border border-neutral-700 bg-neutral-900 px-3 py-2 text-sm text-neutral-100" />
          </div>
        </div>
      </FormModal>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import MainLayout from '@/Layouts/MainLayout.vue'
import Input from '@/Components/ui/Input.vue'
import Select from '@/Components/ui/Select.vue'
import Button from '@/Components/ui/Button.vue'
import FormModal from '@/Components/ui/FormModal.vue'
import axios from '@/axios'
import { useToast } from '@/composables/useToast'

const filters = ref({ user_id: '', entidade_id: '' })
const events = ref([])
const users = ref([])
const entidades = ref([])
const tipos = ref([])
const acoes = ref([])
const modalOpen = ref(false)
const { pushToast } = useToast()
const activity = ref({
  data: '',
  hora: '',
  duracao_minutos: 60,
  user_id: '',
  entidade_id: '',
  tipo_id: '',
  acao_id: '',
  partilha: false,
  conhecimento: false,
  estado: true,
  descricao: ''
})

const calendarOptions = ref({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  events
})

const loadEvents = async () => {
  const response = await axios.get('/calendario', { params: filters.value })
  events.value = response.data.data.map((e) => ({
    id: e.id,
    title: `${e.tipo_nome || 'Atividade'} - ${e.entidade_nome || 'Sem entidade'}`,
    start: `${e.data}T${e.hora || '09:00:00'}`
  }))
}

const loadOptions = async () => {
  const [usersRes, entidadesRes, tiposRes, acoesRes] = await Promise.all([
    axios.get('/access/users'),
    axios.get('/entidades'),
    axios.get('/lookups/calendario-tipos'),
    axios.get('/lookups/calendario-acoes')
  ])
  users.value = usersRes.data.data || []
  entidades.value = entidadesRes.data.data || []
  tipos.value = tiposRes.data || []
  acoes.value = acoesRes.data || []
}

const saveActivity = async () => {
  try {
    await axios.post('/calendario', {
      data: activity.value.data,
      hora: activity.value.hora || null,
      duracao_minutos: Number(activity.value.duracao_minutos || 60),
      user_id: Number(activity.value.user_id),
      entidade_id: activity.value.entidade_id ? Number(activity.value.entidade_id) : null,
      tipo_id: activity.value.tipo_id ? Number(activity.value.tipo_id) : null,
      acao_id: activity.value.acao_id ? Number(activity.value.acao_id) : null,
      partilha: !!activity.value.partilha,
      conhecimento: !!activity.value.conhecimento,
      estado: !!activity.value.estado,
      descricao: activity.value.descricao || null
    })
    pushToast({ type: 'success', title: 'Calendário', message: 'Atividade agendada com sucesso.' })
    modalOpen.value = false
    activity.value = { data: '', hora: '', duracao_minutos: 60, user_id: '', entidade_id: '', tipo_id: '', acao_id: '', partilha: false, conhecimento: false, estado: true, descricao: '' }
    await loadEvents()
  } catch (error) {
    pushToast({ type: 'error', title: 'Calendário', message: error?.response?.data?.message || 'Erro ao agendar atividade.' })
  }
}

loadEvents()
loadOptions()
</script>
