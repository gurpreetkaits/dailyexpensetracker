<template v-if="token">
    <nav class="fixed bottom-5 left-1/2 -translate-x-1/2 bg-white/95 backdrop-blur-xl border border-gray-200/50 rounded-full flex items-center py-2 px-2 shadow-lg shadow-gray-200/50 z-50" style="width: calc(100% - 2rem); max-width: 420px;">
        <!-- Nav items -->
        <router-link
            v-for="item in navigation"
            :key="item.path"
            :to="item.path"
            class="flex-1 flex items-center justify-center w-11 h-11 rounded-full transition-all duration-200"
            :class="$route.path === item.path
                ? 'text-gray-900 bg-gray-100'
                : 'text-gray-400 hover:text-gray-600 active:bg-gray-50'">
            <component :is="item.icon" class="w-5 h-5" :stroke-width="$route.path === item.path ? 2.5 : 1.5" />
        </router-link>

        <!-- Add button integrated in nav -->
        <button
            @click="handleAddClick"
            class="flex items-center justify-center w-12 h-12 ml-1 rounded-full bg-emerald-500 text-white shadow-md hover:bg-emerald-600 active:scale-95 transition-all"
        >
            <Plus class="w-5 h-5" />
        </button>
    </nav>
</template>

<script>
import { Home, LayoutGrid, PieChart, MessageCircle, Wallet, Settings, Plus } from 'lucide-vue-next';
import { useAuthStore } from '../../store/auth';
import { mapState } from 'pinia';

export default {
    name: 'MobileNav',
    components: {
        Home,
        LayoutGrid,
        PieChart,
        MessageCircle,
        Wallet,
        Settings,
        Plus
    },
    computed: {
        ...mapState(useAuthStore, ['token'])
    },
    data() {
        return {
            navigation: [
                { path: '/overview', name: 'Home', icon: Home },
                { path: '/overview2', name: 'Overview2', icon: LayoutGrid },
                { path: '/stats', name: 'Stats', icon: PieChart },
                { path: '/chat', name: 'Chat', icon: MessageCircle },
                { path: '/wallets', name: 'Wallets', icon: Wallet },
                { path: '/settings', name: 'Settings', icon: Settings },
            ],
        }
    },
    methods: {
        handleAddClick() {
            // Dispatch global event for add action
            window.dispatchEvent(new CustomEvent('nav-add-clicked'))
        }
    }
}
</script>
