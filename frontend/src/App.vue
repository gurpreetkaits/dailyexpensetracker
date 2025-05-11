<template>
    <div class="min-h-screen bg-gray-100">
    <NotificationContainer />
        <Header />
        
        <div class="flex min-h-[calc(100vh-4rem)]">
            <!-- Desktop Sidebar -->
            <aside class="hidden lg:block fixed left-0 top-16 h-[calc(100vh-4rem)] w-48 bg-white shadow">
                <DesktopNav v-if="showFooter" />
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1 lg:ml-48 bg-gray-100">
                <div class="lg:px-10 lg:py-6">
                    <router-view />
                </div>
            </main>
        </div>

        <!-- Mobile Navigation -->
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

<style>
/* Ensure the mobile nav stays at the bottom and doesn't overlap content */
@media (max-width: 1024px) {
  main {
    padding-bottom: 4rem; /* Height of mobile nav */
  }
}
</style>
