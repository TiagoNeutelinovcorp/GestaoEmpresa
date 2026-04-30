import { createRouter, createWebHistory } from 'vue-router'
import ClientesIndex from './Pages/Clientes/Index.vue'
import FornecedoresIndex from './Pages/Fornecedores/Index.vue'
import CalendarioIndex from './Pages/Calendario/Index.vue'
import PropostasIndex from './Pages/Propostas/Index.vue'
import EncomendasClientesIndex from './Pages/EncomendasClientes/Index.vue'
import EncomendasFornecedoresIndex from './Pages/EncomendasFornecedores/Index.vue'
import FaturasFornecedores from './Pages/Financeiro/FaturasFornecedores.vue'
import DashboardIndex from './Pages/Dashboard/Index.vue'
import ContactosIndex from './Pages/Contactos/Index.vue'
import UtilizadoresPage from './Pages/Acessos/Utilizadores.vue'
import PermissoesPage from './Pages/Acessos/Permissoes.vue'
import PaisesPage from './Pages/Configuracoes/Paises.vue'
import LookupList from './Pages/Configuracoes/LookupList.vue'
import ArtigosPage from './Pages/Configuracoes/Artigos.vue'
import IvaPage from './Pages/Configuracoes/Iva.vue'
import LogsPage from './Pages/Configuracoes/Logs.vue'
import EmpresaPage from './Pages/Configuracoes/Empresa.vue'
import ContasBancariasPage from './Pages/Financeiro/ContasBancarias.vue'
import ContaCorrenteClientesPage from './Pages/Financeiro/ContaCorrenteClientes.vue'
import ArquivoDigitalPage from './Pages/ArquivoDigital/Index.vue'
import OrdensTrabalhoPage from './Pages/OrdensTrabalho/Index.vue'
import PerfilPage from './Pages/Perfil/Index.vue'
import { useAuth } from './composables/useAuth'
import AccessDenied from './Pages/Auth/AccessDenied.vue'

const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/sem-acesso', component: AccessDenied },
  { path: '/dashboard', component: DashboardIndex, meta: { permission: 'dashboard.read' } },
  { path: '/clientes', component: ClientesIndex, meta: { permission: 'clientes.read' } },
  { path: '/fornecedores', component: FornecedoresIndex, meta: { permission: 'fornecedores.read' } },
  { path: '/contactos', component: ContactosIndex, meta: { permission: 'contactos.read' } },
  { path: '/propostas', component: PropostasIndex, meta: { permission: 'propostas.read' } },
  { path: '/calendario', component: CalendarioIndex, meta: { permission: 'calendario.read' } },
  { path: '/encomendas-clientes', component: EncomendasClientesIndex, meta: { permission: 'encomendas_clientes.read' } },
  { path: '/encomendas-fornecedores', component: EncomendasFornecedoresIndex, meta: { permission: 'encomendas_fornecedores.read' } },
  { path: '/ordens-trabalho', component: OrdensTrabalhoPage, meta: { permission: 'ordens_trabalho.read' } },
  { path: '/financeiro/contas-bancarias', component: ContasBancariasPage, meta: { permission: 'financeiro.read' } },
  { path: '/financeiro/conta-corrente-clientes', component: ContaCorrenteClientesPage, meta: { permission: 'financeiro.read' } },
  { path: '/financeiro/faturas-fornecedores', component: FaturasFornecedores, meta: { permission: 'financeiro.read' } },
  { path: '/arquivo-digital', component: ArquivoDigitalPage, meta: { permission: 'arquivo_digital.read' } },
  { path: '/perfil', component: PerfilPage },
  { path: '/acessos/utilizadores', component: UtilizadoresPage, meta: { permission: 'utilizadores.read' } },
  { path: '/acessos/permissoes', component: PermissoesPage, meta: { permission: 'permissoes.read' } },
  { path: '/configuracoes/paises', component: PaisesPage, meta: { permission: 'configuracoes.read' } },
  { path: '/configuracoes/funcoes-contactos', component: LookupList, props: { title: 'Configurações - Contactos - Funções', type: 'funcoes-contacto' }, meta: { permission: 'configuracoes.read' } },
  { path: '/configuracoes/tipos-calendario', component: LookupList, props: { title: 'Configurações - Calendário - Tipos', type: 'calendario-tipos' }, meta: { permission: 'configuracoes.read' } },
  { path: '/configuracoes/acoes-calendario', component: LookupList, props: { title: 'Configurações - Calendário - Ações', type: 'calendario-acoes' }, meta: { permission: 'configuracoes.read' } },
  { path: '/configuracoes/artigos', component: ArtigosPage, meta: { permission: 'configuracoes.read' } },
  { path: '/configuracoes/iva', component: IvaPage, meta: { permission: 'configuracoes.read' } },
  { path: '/configuracoes/logs', component: LogsPage, meta: { permission: 'logs.read' } },
  { path: '/configuracoes/empresa', component: EmpresaPage, meta: { permission: 'configuracoes.read' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to) => {
  const { loadAuth, hasPermission, user } = useAuth()
  await loadAuth()

  const required = to.meta?.permission
  if (!required) return true
  if (hasPermission(required)) return true

  if (!user.value) return '/login'

  if (to.path !== '/dashboard' && hasPermission('dashboard.read')) {
    return '/dashboard'
  }

  if (to.path !== '/sem-acesso') {
    return '/sem-acesso'
  }

  return true
})

export default router
