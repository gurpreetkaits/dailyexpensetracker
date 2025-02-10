<template v-if="token">
  <nav class="fixed bottom-0 w-full bg-white border-t flex justify-around p-4">
    <router-link v-for="item in navigation" :key="item.path" :to="item.path" class="flex flex-col items-center"
      :class="{ 'text-blue-600': $route.path === item.path }">
      <component :is="item.icon"></component>
      <span class="text-xs mt-1">{{ item.name }}</span>
    </router-link>
  </nav>
</template>

<script>
import { Settings, Home, User, BarChart, Target } from 'lucide-vue-next';
import { useAuthStore } from '../../store/auth';
import { mapActions, mapState } from 'pinia';
export default {
  name: 'Footer',
  computed: {
    ...mapState(useAuthStore, ['token'])
  },
  data() {
    return {
      navigation: [
        { path: '/overview', name: 'Home', icon: Home },
        { path: '/stats', name: 'Stats', icon: BarChart },
        // { path: '/goals', name: 'Goals', icon: Target },
        { path: '/profile', name: 'Profile', icon: User },
        { path: '/settings', name: 'Settings', icon: Settings },
      ],
    }
  }
}
</script>
