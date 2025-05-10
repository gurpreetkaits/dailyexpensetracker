<template v-if="token">
    <nav class="fixed bottom-0 w-full bg-white border-t flex justify-around py-2 px-1 lg:hidden">
        <router-link v-for="item in navigation" :key="item.path" :to="item.path" class="flex flex-col items-center"
                     :class="{ 'text-blue-600': $route.path === item.path }">
            <component :is="item.icon" class="w-5 h-5" />
            <span class="text-[10px] mt-0.5">{{ item.name }}</span>
        </router-link>
    </nav>
</template>

<script>
import { Settings, Home, User, BarChart, Target, MessageCircle } from 'lucide-vue-next';
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
                { path: '/chat', name: 'Chat', icon: MessageCircle },
                { path: '/settings', name: 'Settings', icon: Settings },
            ],
        }
    }
}
</script>
