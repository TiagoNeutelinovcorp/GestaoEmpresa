<template>
  <MainLayout>
    <div class="mx-auto max-w-3xl space-y-6">
      <div>
        <h1 class="text-2xl font-bold">Perfil</h1>
        <p class="text-sm text-neutral-400">Gerir palavra-passe e autenticação de dois fatores (Google Authenticator).</p>
      </div>

      <section class="space-y-3 rounded-md border border-neutral-800 bg-black p-4">
        <h2 class="text-lg font-semibold">Dados da Conta</h2>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium">Nome</label>
            <Input :model-value="authUser?.name || ''" disabled />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium">Email</label>
            <Input :model-value="authUser?.email || ''" disabled />
          </div>
        </div>
      </section>

      <section class="space-y-3 rounded-md border border-neutral-800 bg-black p-4">
        <h2 class="text-lg font-semibold">Alterar Palavra-passe</h2>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
          <Input v-model="passwordForm.current_password" type="password" placeholder="Palavra-passe atual" />
          <div />
          <Input v-model="passwordForm.password" type="password" placeholder="Nova palavra-passe" />
          <Input v-model="passwordForm.password_confirmation" type="password" placeholder="Confirmar nova palavra-passe" />
        </div>
        <div>
          <Button @click="updatePassword">Guardar nova palavra-passe</Button>
        </div>
      </section>

      <section class="space-y-3 rounded-md border border-neutral-800 bg-black p-4">
        <h2 class="text-lg font-semibold">Autenticação 2FA</h2>
        <p class="text-sm text-neutral-400">
          Estado:
          <span class="font-medium text-neutral-200">{{ twoFactorEnabled ? 'Ativada' : 'Desativada' }}</span>
        </p>

        <div class="flex flex-wrap gap-2">
          <Button v-if="!twoFactorEnabled" @click="enableTwoFactor">Ativar 2FA</Button>
          <Button v-if="twoFactorEnabled" variant="outline" @click="refreshTwoFactorData">Atualizar QR/Códigos</Button>
          <Button v-if="twoFactorEnabled" variant="secondary" @click="regenerateRecoveryCodes">Regenerar Códigos</Button>
          <Button v-if="twoFactorEnabled" variant="destructive" @click="disableTwoFactor">Desativar 2FA</Button>
        </div>

        <div v-if="twoFactorEnabled" class="space-y-3 rounded-md border border-neutral-800 p-3">
          <p class="text-sm text-neutral-300">
            1) Abre Google Authenticator e lê o QR Code. 2) Introduz um código de 6 dígitos para confirmar.
          </p>

          <div v-if="qrCodeSvg" class="rounded-md bg-white p-3" v-html="qrCodeSvg"></div>

          <div class="flex flex-col gap-2 md:flex-row">
            <Input v-model="confirmCode" placeholder="Código de 6 dígitos" />
            <Button @click="confirmTwoFactor">Confirmar 2FA</Button>
          </div>

          <div>
            <p class="mb-2 text-sm font-medium">Códigos de recuperação</p>
            <div class="space-y-1 rounded-md border border-neutral-800 bg-neutral-950 p-3 font-mono text-xs">
              <div v-for="code in recoveryCodes" :key="code">{{ code }}</div>
              <div v-if="recoveryCodes.length === 0" class="text-neutral-400">Sem códigos disponíveis.</div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <FormModal
      :is-open="confirmPasswordModalOpen"
      title="Confirmar palavra-passe"
      confirm-text="Confirmar"
      @close="closeConfirmPasswordModal"
      @confirm="submitPasswordConfirmation"
    >
      <div class="space-y-3">
        <p class="text-sm text-neutral-300">
          Por segurança, confirma a tua palavra-passe para continuar esta operação de 2FA.
        </p>
        <Input v-model="confirmPasswordValue" type="password" placeholder="Palavra-passe atual" />
      </div>
    </FormModal>
  </MainLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'
import MainLayout from '@/Layouts/MainLayout.vue'
import Input from '@/Components/ui/Input.vue'
import Button from '@/Components/ui/Button.vue'
import FormModal from '@/Components/ui/FormModal.vue'
import api from '@/axios'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'

const { user } = useAuth()
const { pushToast } = useToast()

const authUser = ref(null)
const twoFactorEnabled = ref(false)
const qrCodeSvg = ref('')
const recoveryCodes = ref([])
const confirmCode = ref('')
const confirmPasswordModalOpen = ref(false)
const confirmPasswordValue = ref('')
const pendingTwoFactorAction = ref(null)
const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const webClient = axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest'
  }
})

const loadMe = async () => {
  const res = await api.get('/me')
  authUser.value = res.data
}

const refreshTwoFactorData = async () => {
  try {
    const qr = await webClient.get('/user/two-factor-qr-code')
    const codes = await webClient.get('/user/two-factor-recovery-codes')
    qrCodeSvg.value = qr.data?.svg || ''
    recoveryCodes.value = Array.isArray(codes.data) ? codes.data : []
    twoFactorEnabled.value = true
  } catch {
    twoFactorEnabled.value = false
    qrCodeSvg.value = ''
    recoveryCodes.value = []
  }
}

const withPasswordConfirmation = async (action) => {
  try {
    await action()
    return true
  } catch (error) {
    const status = error?.response?.status
    const message = String(error?.response?.data?.message || '').toLowerCase()
    const requiresConfirmation = status === 423 || message.includes('password confirmation required')

    if (requiresConfirmation) {
      pendingTwoFactorAction.value = action
      confirmPasswordModalOpen.value = true
      return false
    }
    throw error
  }
}

const closeConfirmPasswordModal = () => {
  confirmPasswordModalOpen.value = false
  confirmPasswordValue.value = ''
  pendingTwoFactorAction.value = null
}

const submitPasswordConfirmation = async () => {
  try {
    await webClient.post('/user/confirm-password', { password: confirmPasswordValue.value })
    const action = pendingTwoFactorAction.value
    closeConfirmPasswordModal()
    if (action) {
      await action()
    }
  } catch (error) {
    pushToast({ type: 'error', title: 'Perfil', message: error?.response?.data?.message || 'Palavra-passe inválida.' })
  }
}

const enableTwoFactor = async () => {
  try {
    const done = await withPasswordConfirmation(async () => {
      await webClient.post('/user/two-factor-authentication')
      await refreshTwoFactorData()
    })
    if (done) {
      pushToast({ type: 'success', title: 'Perfil', message: '2FA ativada. Confirma com o código do Authenticator.' })
    }
  } catch (error) {
    pushToast({ type: 'error', title: 'Perfil', message: error?.response?.data?.message || 'Erro ao ativar 2FA.' })
  }
}

const confirmTwoFactor = async () => {
  try {
    await webClient.post('/user/confirmed-two-factor-authentication', { code: confirmCode.value })
    confirmCode.value = ''
    pushToast({ type: 'success', title: 'Perfil', message: '2FA confirmada com sucesso.' })
  } catch (error) {
    pushToast({ type: 'error', title: 'Perfil', message: error?.response?.data?.message || 'Código inválido.' })
  }
}

const regenerateRecoveryCodes = async () => {
  try {
    const done = await withPasswordConfirmation(async () => {
      await webClient.post('/user/two-factor-recovery-codes')
      await refreshTwoFactorData()
    })
    if (done) {
      pushToast({ type: 'success', title: 'Perfil', message: 'Códigos de recuperação regenerados.' })
    }
  } catch (error) {
    pushToast({ type: 'error', title: 'Perfil', message: error?.response?.data?.message || 'Erro ao regenerar códigos.' })
  }
}

const disableTwoFactor = async () => {
  try {
    const done = await withPasswordConfirmation(async () => {
      await webClient.delete('/user/two-factor-authentication')
      twoFactorEnabled.value = false
      qrCodeSvg.value = ''
      recoveryCodes.value = []
      confirmCode.value = ''
    })
    if (done) {
      pushToast({ type: 'success', title: 'Perfil', message: '2FA desativada.' })
    }
  } catch (error) {
    pushToast({ type: 'error', title: 'Perfil', message: error?.response?.data?.message || 'Erro ao desativar 2FA.' })
  }
}

const updatePassword = async () => {
  try {
    await webClient.put('/user/password', passwordForm.value)
    passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
    pushToast({ type: 'success', title: 'Perfil', message: 'Palavra-passe atualizada com sucesso.' })
  } catch (error) {
    pushToast({ type: 'error', title: 'Perfil', message: error?.response?.data?.message || 'Erro ao atualizar palavra-passe.' })
  }
}

onMounted(async () => {
  authUser.value = user.value
  if (!authUser.value) {
    await loadMe()
  }
  await refreshTwoFactorData()
})
</script>
