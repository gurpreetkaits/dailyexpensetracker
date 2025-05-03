<template>
    <div class="min-h-screen bg-gray-100">
    <NotificationContainer />
        <Header />
        <DesktopNav v-if="showFooter" />
        <main>
            <router-view />
        </main>
        <MobileNav v-if="showFooter" />
    </div>
</template>

<script>
import { verifyToken } from './services/AuthService';
import { useAuthStore } from './store/auth'
import Header from './components/Layout/Header.vue';
import DesktopNav from './components/Layout/DesktopNav.vue';
import MobileNav from './components/Layout/MobileNav.vue';
import { mapActions } from 'pinia';
import { useSettingsStore } from './store/settings';
import NotificationContainer from './components/Global/NotificationContainer.vue';

export default {
  name: 'App',
  components: {
    Header,NotificationContainer,
    MobileNav, DesktopNav
  },

  methods: {
    ...mapActions(useSettingsStore, ['fetchSettings'])
  },
  computed: {
    showFooter() {
      return this.$route.name !== 'login' && useAuthStore().token
    }
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
