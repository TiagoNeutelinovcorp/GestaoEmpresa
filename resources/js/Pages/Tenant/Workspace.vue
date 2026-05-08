<template>
  <MainLayout>
    <div class="mx-auto max-w-5xl space-y-6">
      <div>
        <h1 class="text-2xl font-bold">Tenant Workspace</h1>
        <p class="text-sm text-neutral-400">Onboarding, planos, limites e utilização do tenant ativo.</p>
      </div>

      <section class="rounded-md border border-neutral-800 bg-black p-4 space-y-3">
        <h2 class="text-lg font-semibold">Onboarding Self-Service</h2>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
          <label class="flex items-center gap-2 rounded border border-neutral-800 p-3">
            <input v-model="onboarding.branding_completed" type="checkbox">
            <span>Branding configurado</span>
          </label>
          <label class="flex items-center gap-2 rounded border border-neutral-800 p-3">
            <input v-model="onboarding.users_completed" type="checkbox">
            <span>Utilizadores configurados</span>
          </label>
          <label class="flex items-center gap-2 rounded border border-neutral-800 p-3">
            <input v-model="onboarding.permissions_completed" type="checkbox">
            <span>Permissões configuradas</span>
          </label>
        </div>
        <div class="flex justify-end">
          <Button @click="saveOnboarding">Guardar checklist</Button>
        </div>
      </section>

      <section class="rounded-md border border-neutral-800 bg-black p-4 space-y-3">
        <h2 class="text-lg font-semibold">Subscrição e Trial</h2>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
          <div class="rounded border border-neutral-800 p-3">
            <p class="text-xs text-neutral-400">Plano atual</p>
            <p class="font-semibold">{{ subscription.plan_nome || '-' }}</p>
          </div>
          <div class="rounded border border-neutral-800 p-3">
            <p class="text-xs text-neutral-400">Estado</p>
            <p class="font-semibold">{{ subscription.status || '-' }}</p>
          </div>
          <div class="rounded border border-neutral-800 p-3">
            <p class="text-xs text-neutral-400">Trial (dias restantes)</p>
            <p class="font-semibold">{{ usage.trial_days_left ?? 0 }}</p>
          </div>
          <div class="rounded border border-neutral-800 p-3">
            <p class="text-xs text-neutral-400">Premium</p>
            <p class="font-semibold">{{ usage.premium_enabled ? 'Ativo' : 'Inativo' }}</p>
          </div>
        </div>
      </section>

      <section class="rounded-md border border-neutral-800 bg-black p-4 space-y-3">
        <h2 class="text-lg font-semibold">Limites e Utilização</h2>
        <div class="rounded border border-neutral-800 p-3">
          <p>Utilizadores: <strong>{{ usage.users?.used ?? 0 }}</strong> / {{ usage.users?.limit ?? 0 }}</p>
          <p class="text-sm text-neutral-400">Disponíveis: {{ usage.users?.remaining ?? 0 }}</p>
        </div>
      </section>

      <section class="rounded-md border border-neutral-800 bg-black p-4 space-y-3">
        <h2 class="text-lg font-semibold">Planos e Upgrade / Downgrade</h2>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
          <div v-for="plan in plans" :key="plan.id" class="rounded border border-neutral-800 p-3 space-y-2">
            <p class="font-semibold">{{ plan.nome }}</p>
            <p class="text-sm text-neutral-400">{{ Number(plan.preco_mensal).toFixed(2) }} €/mês</p>
            <p class="text-sm text-neutral-400">Limite users: {{ plan?.limites?.max_users ?? '-' }}</p>
            <Button
              size="sm"
              :variant="Number(subscription.plan_id) === Number(plan.id) ? 'secondary' : 'default'"
              :disabled="Number(subscription.plan_id) === Number(plan.id)"
              @click="changePlan(plan.id)"
            >
              {{ Number(subscription.plan_id) === Number(plan.id) ? 'Plano atual' : 'Escolher plano' }}
            </Button>
          </div>
        </div>
      </section>

      <section class="rounded-md border border-neutral-800 bg-black p-4 space-y-3">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold">Logs de alterações de plano</h2>
          <Button size="sm" variant="destructive" @click="cancelSubscription">Cancelar subscrição</Button>
        </div>
        <DataTable :columns="logColumns" :data="logs" />
      </section>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import Button from '@/Components/ui/Button.vue'
import DataTable from '@/Components/ui/DataTable.vue'
import axios from '@/axios'
import { useToast } from '@/composables/useToast'

const { pushToast } = useToast()
const onboarding = ref({
  branding_completed: false,
  users_completed: false,
  permissions_completed: false
})
const subscription = ref({})
const usage = ref({})
const plans = ref([])
const logs = ref([])

const logColumns = [
  { header: 'Data', accessorKey: 'created_at' },
  { header: 'Utilizador', accessorKey: 'user_name' },
  { header: 'Tipo', accessorKey: 'change_type' },
  { header: 'De', accessorKey: 'from_plan_nome' },
  { header: 'Para', accessorKey: 'to_plan_nome' },
  { header: 'Pró-rata', accessorKey: 'prorata_amount' }
]

const loadAll = async () => {
  const [onb, sub, use, pls, lgs] = await Promise.all([
    axios.get('/tenant/onboarding'),
    axios.get('/billing/subscription'),
    axios.get('/billing/usage'),
    axios.get('/billing/plans'),
    axios.get('/billing/change-logs')
  ])
  onboarding.value = {
    branding_completed: Boolean(onb.data.branding_completed),
    users_completed: Boolean(onb.data.users_completed),
    permissions_completed: Boolean(onb.data.permissions_completed)
  }
  subscription.value = sub.data
  usage.value = use.data
  plans.value = pls.data
  logs.value = lgs.data.data || []
}

const saveOnboarding = async () => {
  try {
    await axios.put('/tenant/onboarding', onboarding.value)
    pushToast({ type: 'success', title: 'Tenant', message: 'Checklist guardada.' })
    await loadAll()
  } catch (error) {
    pushToast({ type: 'error', title: 'Tenant', message: error?.response?.data?.message || 'Erro ao guardar checklist.' })
  }
}

const changePlan = async (planId) => {
  try {
    const res = await axios.post('/billing/change-plan', { plan_id: Number(planId) })
    pushToast({ type: 'success', title: 'Billing', message: res.data?.message || 'Plano atualizado.' })
    await loadAll()
  } catch (error) {
    pushToast({ type: 'error', title: 'Billing', message: error?.response?.data?.message || 'Erro ao alterar plano.' })
  }
}

const cancelSubscription = async () => {
  if (!window.confirm('Cancelar subscrição deste tenant?')) return
  try {
    await axios.post('/billing/cancel')
    pushToast({ type: 'success', title: 'Billing', message: 'Subscrição cancelada.' })
    await loadAll()
  } catch (error) {
    pushToast({ type: 'error', title: 'Billing', message: error?.response?.data?.message || 'Erro ao cancelar subscrição.' })
  }
}

loadAll()
</script>

