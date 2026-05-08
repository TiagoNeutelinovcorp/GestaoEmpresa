import { computed, ref } from 'vue'
import axios from '@/axios'

const user = ref(null)
const permissions = ref([])
const tenants = ref([])
const activeTenant = ref(null)
const loaded = ref(false)

export function useAuth() {
  const loadAuth = async (force = false) => {
    if (loaded.value && !force) return
    if (force) loaded.value = false
    try {
      const res = await axios.get('/me')
      user.value = res.data
      permissions.value = res.data.permissions || []
      tenants.value = res.data.tenants || []
      activeTenant.value = res.data.active_tenant || null
    } catch (e) {
      user.value = null
      permissions.value = []
      tenants.value = []
      activeTenant.value = null
    } finally {
      loaded.value = true
    }
  }

  const hasPermission = (permission) => permissions.value.includes(permission)

  const switchTenant = async (tenantId) => {
    await axios.post('/tenants/switch', { tenant_id: Number(tenantId) })
    await loadAuth(true)
  }

  const createTenant = async (payload) => {
    await axios.post('/tenants', payload)
    await loadAuth(true)
  }

  return {
    user: computed(() => user.value),
    permissions: computed(() => permissions.value),
    tenants: computed(() => tenants.value),
    activeTenant: computed(() => activeTenant.value),
    loaded: computed(() => loaded.value),
    loadAuth,
    hasPermission,
    switchTenant,
    createTenant
  }
}
