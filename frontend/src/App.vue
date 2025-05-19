<template>
    <div class="min-h-screen bg-gray-100">
    <NotificationContainer />
        <Header />
        
        <div class="flex min-h-[calc(100vh-4rem)]">
            <!-- Desktop Sidebar -->
            <aside v-if="showDesktopNav" class="hidden lg:block fixed left-0 top-16 h-[calc(100vh-4rem)] w-48 bg-white shadow">
                <DesktopNav />
            </aside>

            <!-- Main Content Area -->
            <main :class="showDesktopNav ? 'flex-1 lg:ml-48 bg-gray-100' : 'flex-1 flex items-center justify-center bg-gray-50'">
                <div v-if="showDesktopNav" class="lg:px-10 lg:py-6">
                    <SubscriptionGuard>
                        <router-view />
                    </SubscriptionGuard>
                </div>
                <div v-else class="w-full">
                    <SubscriptionGuard>
                        <router-view />
                    </SubscriptionGuard>
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
import SubscriptionGuard from './components/SubscriptionGuard.vue'

export default {
  name: 'App',
  components: {
    Header,NotificationContainer,
    MobileNav, DesktopNav,
    SubscriptionGuard
  },

  methods: {
    ...mapActions(useSettingsStore, ['fetchSettings'])
  },
  computed: {
    showFooter() {
      return this.$route.name !== 'login' && useAuthStore().token
    },
    showDesktopNav() {
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
