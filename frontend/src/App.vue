<template>
    <router-view />
    <AdCashAuto v-if="showAds" />
    <FloatingFeedbackButton v-if="showFeedbackButton" />
    <NotificationContainer />
</template>

<script>
import { useAuthStore } from './store/auth'
import { mapActions, mapState } from 'pinia'
import { useSettingsStore } from './store/settings'
import { useThemeStore } from './store/theme'
import { verifyToken } from './services/AuthService'
import { usePolarStore } from './store/polar'
import AdCashAuto from './components/Subscription/AdCashAuto.vue'
import FloatingFeedbackButton from './components/FloatingFeedbackButton.vue'
import NotificationContainer from './components/Global/NotificationContainer.vue'

export default {
  name: 'App',
  components: {
    AdCashAuto,
    FloatingFeedbackButton,
    NotificationContainer
  },
  data() {
    return {
      showAds: false
    }
  },
  computed: {
    ...mapState(usePolarStore, ['hasActiveSubscription']),
    showFeedbackButton() {
      return this.$route.path !== '/login';
    }
  },
  methods: {
    ...mapActions(useSettingsStore, ['fetchSettings']),
    ...mapActions(useThemeStore, ['initTheme'])
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
    
    // Initialize theme
    this.initTheme()
  },
  async mounted() {
  const polarStore = usePolarStore()
  await polarStore.fetchSubscriptionStatus()
  this.showAds = !polarStore.hasActiveSubscription
}
}
</script>
