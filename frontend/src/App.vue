<template>
    <router-view />
    <AdCashAuto v-if="showAds" />
</template>

<script>
import { useAuthStore } from './store/auth'
import { mapActions, mapState } from 'pinia'
import { useSettingsStore } from './store/settings'
import { verifyToken } from './services/AuthService'
import { usePolarStore } from './store/polar'
import AdCashAuto from './components/Subscription/AdCashAuto.vue'

export default {
  name: 'App',
  components: {
    AdCashAuto
  },
  data() {
    return {
      showAds: false
    }
  },
  computed: {
    ...mapState(usePolarStore, ['hasActiveSubscription'])
  },
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
  },
  async mounted() {
  const polarStore = usePolarStore()
  await polarStore.fetchSubscriptionStatus()
  this.showAds = !polarStore.hasActiveSubscription
}
}
</script>
