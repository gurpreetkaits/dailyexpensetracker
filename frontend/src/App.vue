<template>
    <SplashLoader />
    <router-view />
    <AdCashAuto v-if="showAds" />
    <NotificationContainer />
</template>

<script>
import { useAuthStore } from './store/auth'
import { mapActions, mapState } from 'pinia'
import { useSettingsStore } from './store/settings'
import { useThemeStore } from './store/theme'
import { useLoaderStore } from './store/loader'
import { verifyToken } from './services/AuthService'
import { usePolarStore } from './store/polar'
import AdCashAuto from './components/Subscription/AdCashAuto.vue'
import NotificationContainer from './components/Global/NotificationContainer.vue'
import SplashLoader from './components/Global/SplashLoader.vue'

export default {
  name: 'App',
  components: {
    AdCashAuto,
    NotificationContainer,
    SplashLoader
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
    ...mapActions(useSettingsStore, ['fetchSettings']),
    ...mapActions(useThemeStore, ['initTheme'])
  },
  async created() {
    const authStore = useAuthStore()
    const loaderStore = useLoaderStore()

    loaderStore.showLoader()

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

    // Hide loader after initial setup
    setTimeout(() => {
      loaderStore.hideLoader()
    }, 500)
  },
  async mounted() {
    const polarStore = usePolarStore()
    await polarStore.fetchSubscriptionStatus()
    this.showAds = !polarStore.hasActiveSubscription
  }
}
</script>
