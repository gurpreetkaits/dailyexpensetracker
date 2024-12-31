<template>
  <div class="min-h-screen bg-gray-100">
    <Header />
    <main>
      <router-view>
      </router-view>
    </main>
    <Footer v-if="showFooter" />
  </div>
</template>

<script>
import { verifyToken } from './services/AuthService';
import { useAuthStore } from './store/auth'
import Header from './components/Layout/Header.vue';
import Footer from './components/Layout/Footer.vue';
import { mapActions } from 'pinia';
import { useSettingsStore } from './store/settings';

export default {
  name: 'App',
  components: {
    Header,
    Footer
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
