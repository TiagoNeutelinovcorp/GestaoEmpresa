<template>
  <div class="min-h-screen bg-neutral-950 text-neutral-100">
    <header class="border-b border-neutral-800 bg-black shadow-sm">
      <div class="flex h-16 items-center justify-between px-6">
        <h1 class="text-xl font-bold text-neutral-100">Gestão App</h1>
        <div class="flex items-center gap-2">
          <a
            href="/perfil"
            class="inline-flex items-center gap-2 rounded-md border border-neutral-700 bg-neutral-900 px-3 py-1.5 text-sm text-neutral-100 hover:bg-neutral-800"
            title="Perfil"
          >
            <span>Perfil</span>
          </a>
          <a
            href="/logout"
            class="rounded-md border border-neutral-700 bg-neutral-900 px-3 py-1.5 text-sm text-neutral-100 hover:bg-neutral-800"
          >
            Terminar sessão
          </a>
        </div>
      </div>
    </header>

    <div class="flex">
      <aside class="min-h-[calc(100vh-64px)] w-64 border-r border-neutral-800 bg-black">
        <nav class="p-4 space-y-1 max-h-[calc(100vh-64px)] overflow-y-auto">
          <RouterLink
            v-for="item in visibleMenuItems"
            :key="item.path"
            :to="item.path"
            class="block rounded-md px-3 py-2 text-sm font-medium text-neutral-300 hover:bg-neutral-900 hover:text-neutral-100"
            active-class="bg-neutral-900 text-neutral-100"
          >
            {{ item.name }}
          </RouterLink>
        </nav>
      </aside>

      <main class="flex-1 bg-neutral-950 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const menuItems = ref([
  { name: 'Dashboard', path: '/dashboard', permission: 'dashboard.read' },
  { name: 'Clientes', path: '/clientes', permission: 'clientes.read' },
  { name: 'Fornecedores', path: '/fornecedores', permission: 'fornecedores.read' },
  { name: 'Contactos', path: '/contactos', permission: 'contactos.read' },
  { name: 'Propostas', path: '/propostas', permission: 'propostas.read' },
  { name: 'Calendário', path: '/calendario', permission: 'calendario.read' },
  { name: 'Encomendas - Clientes', path: '/encomendas-clientes', permission: 'encomendas_clientes.read' },
  { name: 'Encomendas - Fornecedores', path: '/encomendas-fornecedores', permission: 'encomendas_fornecedores.read' },
  { name: 'Ordens de Trabalho', path: '/ordens-trabalho', permission: 'ordens_trabalho.read' },
  { name: 'Financeiro - Contas Bancárias', path: '/financeiro/contas-bancarias', permission: 'financeiro.read' },
  { name: 'Financeiro - Conta Corrente Clientes', path: '/financeiro/conta-corrente-clientes', permission: 'financeiro.read' },
  { name: 'Financeiro - Faturas Fornecedores', path: '/financeiro/faturas-fornecedores', permission: 'financeiro.read' },
  { name: 'Arquivo Digital', path: '/arquivo-digital', permission: 'arquivo_digital.read' },
  { name: 'Acessos - Utilizadores', path: '/acessos/utilizadores', permission: 'utilizadores.read' },
  { name: 'Acessos - Permissões', path: '/acessos/permissoes', permission: 'permissoes.read' },
  { name: 'Config - Países', path: '/configuracoes/paises', permission: 'configuracoes.read' },
  { name: 'Config - Funções Contactos', path: '/configuracoes/funcoes-contactos', permission: 'configuracoes.read' },
  { name: 'Config - Tipos Calendário', path: '/configuracoes/tipos-calendario', permission: 'configuracoes.read' },
  { name: 'Config - Ações Calendário', path: '/configuracoes/acoes-calendario', permission: 'configuracoes.read' },
  { name: 'Config - Artigos', path: '/configuracoes/artigos', permission: 'configuracoes.read' },
  { name: 'Config - IVA', path: '/configuracoes/iva', permission: 'configuracoes.read' },
  { name: 'Config - Logs', path: '/configuracoes/logs', permission: 'logs.read' },
  { name: 'Config - Empresa', path: '/configuracoes/empresa', permission: 'configuracoes.read' },
])

const { permissions, loadAuth } = useAuth()
const visibleMenuItems = computed(() =>
  menuItems.value.filter((item) => item.permission === null || permissions.value.includes(item.permission))
)

onMounted(() => {
  loadAuth()
})
</script>
