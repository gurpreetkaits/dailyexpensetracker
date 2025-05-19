<template>
    <router-view />
</template>

<script>
import { useAuthStore } from './store/auth'
import { mapActions } from 'pinia'
import { useSettingsStore } from './store/settings'
import { verifyToken } from './services/AuthService'

export default {
  name: 'App',
  methods: {
    ...mapActions(useSettingsStore, ['fetchSettings'])
  },
  async created() {
    const authStore = useAuthStore()
    if (authStore.token) {
      await this.fetchSettings()
      try {
        const user = await verifyToken()
        authStore.setAuth(authStore.token, user)
      } catch (error) {
        authStore.clearAuth()
        this.$router.push('/login')
      }
    }
  }
}
</script>
