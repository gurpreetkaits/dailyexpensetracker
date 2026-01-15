<template v-if="token">
    <nav class="fixed bottom-4 left-1/2 -translate-x-1/2 bg-white/90 backdrop-blur-xl border border-gray-200/50 rounded-full flex justify-around py-3 px-6 lg:hidden shadow-lg shadow-gray-200/50 gap-2">
        <router-link
            v-for="item in navigation"
            :key="item.path"
            :to="item.path"
            class="flex items-center justify-center w-10 h-10 rounded-full transition-all duration-200"
            :class="$route.path === item.path
                ? 'text-gray-900 bg-gray-100'
                : 'text-gray-400 hover:text-gray-600'">
            <component :is="item.icon" class="w-5 h-5" :stroke-width="$route.path === item.path ? 2.5 : 1.5" />
        </router-link>
    </nav>
</template>

<script>
import { Home, PieChart, MessageCircle, Wallet, Settings } from 'lucide-vue-next';
import { useAuthStore } from '../../store/auth';
import { mapState } from 'pinia';

export default {
    name: 'MobileNav',
    components: {
        Home,
        PieChart,
        MessageCircle,
        Wallet,
        Settings
    },
    computed: {
        ...mapState(useAuthStore, ['token'])
    },
    data() {
        return {
            navigation: [
                { path: '/overview', name: 'Home', icon: Home },
                { path: '/stats', name: 'Stats', icon: PieChart },
                { path: '/chat', name: 'Chat', icon: MessageCircle },
                { path: '/wallets', name: 'Wallets', icon: Wallet },
                { path: '/settings', name: 'Settings', icon: Settings },
            ],
        }
    }
}
</script>
