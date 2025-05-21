<template>
    <router-view />
    <AdCashAuto v-if="showAds" />
</template>

<script>
import { useAuthStore } from './store/auth'
import { mapActions } from 'pinia'
import { useSettingsStore } from './store/settings'
import { verifyToken } from './services/AuthService'
import { usePolarStore } from '../store/polar'
import { computed } from 'vue'
import AdCashAuto from './components/Subscription/AdCashAuto.vue'

export default {
  name: 'App',
  data() {
    return {
      showAds: true
    }
  },
  methods: {
    ...mapActions(useSettingsStore, ['fetchSettings'])
  },
  computed: {
    ...mapState(usePolarStore, ['hasActiveSubscription'])
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
  },
  mounted() {
    try {
        const polarStore = usePolarStore()
        await polarStore.fetchSubscriptionStatus()
        if (this.hasActiveSubscription) {
          this.showAds = false
        }
      } catch (error) {
        this.showAds = true
      }
  }
}
</script>
