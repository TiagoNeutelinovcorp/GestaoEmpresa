import { computed, ref } from 'vue'
import axios from '@/axios'

const user = ref(null)
const permissions = ref([])
const loaded = ref(false)

export function useAuth() {
  const loadAuth = async () => {
    if (loaded.value) return
    try {
      const res = await axios.get('/me')
      user.value = res.data
      permissions.value = res.data.permissions || []
    } catch (e) {
      user.value = null
      permissions.value = []
    } finally {
      loaded.value = true
    }
  }

  const hasPermission = (permission) => permissions.value.includes(permission)

  return {
    user: computed(() => user.value),
    permissions: computed(() => permissions.value),
    loaded: computed(() => loaded.value),
    loadAuth,
    hasPermission
  }
}
