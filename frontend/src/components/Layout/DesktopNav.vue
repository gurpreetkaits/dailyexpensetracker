<template>
    <nav class="h-full p-4 flex flex-col justify-between">
        <!-- Main Navigation - Same as Mobile -->
        <div class="bg-gray-50 rounded-2xl p-2">
            <div class="flex flex-col gap-1">
                <router-link
                    v-for="item in navigation"
                    :key="item.path"
                    :to="item.path"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200"
                    :class="$route.path === item.path
                        ? 'text-gray-900 bg-white shadow-sm'
                        : 'text-gray-500 hover:text-gray-700 hover:bg-white/50'">
                    <component :is="item.icon" class="w-5 h-5" :stroke-width="$route.path === item.path ? 2.5 : 1.5" />
                    <span class="text-sm font-medium">{{ item.name }}</span>
                </router-link>
            </div>

            <!-- Add Button -->
            <button
                @click="handleAddClick"
                class="flex items-center justify-center gap-2 w-full mt-2 py-2.5 rounded-xl bg-emerald-500 text-white shadow-sm hover:bg-emerald-600 active:scale-[0.98] transition-all"
            >
                <Plus class="w-5 h-5" />
                <span class="text-sm font-medium">Add</span>
            </button>
        </div>

        <!-- Bottom Section -->
        <div class="space-y-2">
            <button
                @click="handleLogout"
                class="flex items-center gap-3 px-3 py-2.5 w-full rounded-xl hover:bg-red-50 transition-colors text-sm font-medium text-red-600 hover:text-red-700"
            >
                <LogOut class="w-5 h-5" />
                <span>Logout</span>
            </button>

            <!-- Creator Section -->
            <div class="border-t border-gray-200 pt-3">
                <div class="text-xs text-gray-400 mb-2 px-1">Creator</div>

                <a
                    href="https://x.com/gurpreetkait"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-3 px-3 py-2 w-full rounded-xl hover:bg-gray-50 transition-colors text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    <Twitter class="w-4 h-4 text-blue-400" />
                    <span>@gurpreetkait</span>
                </a>

                <a
                    href="https://buymeacoffee.com/gurpreetkash"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-3 px-3 py-2 w-full rounded-xl hover:bg-orange-50 transition-colors text-sm font-medium text-orange-600 hover:text-orange-700"
                >
                    <Coffee class="w-4 h-4" />
                    <span>Buy me a coffee</span>
                </a>
            </div>
        </div>
    </nav>
</template>

<script>
import { Home, PieChart, MessageCircle, Wallet, Settings, Plus, LogOut, Coffee, Twitter } from 'lucide-vue-next';
import { mapState, mapActions } from 'pinia';
import { useAuthStore } from '../../store/auth.js';
import { useRouter } from 'vue-router';

export default {
    name: 'DesktopNav',
    components: { Home, PieChart, MessageCircle, Wallet, Settings, Plus, LogOut, Coffee, Twitter },
    data() {
        return {
            // Same navigation as MobileNav
            navigation: [
                { path: '/overview', name: 'Home', icon: Home },
                { path: '/stats', name: 'Stats', icon: PieChart },
                { path: '/chat', name: 'Chat', icon: MessageCircle },
                { path: '/wallets', name: 'Wallets', icon: Wallet },
                { path: '/settings', name: 'Settings', icon: Settings },
            ],
        };
    },
    setup() {
        const router = useRouter();
        return { router };
    },
    computed: {
        ...mapState(useAuthStore, ['user']),
    },
    methods: {
        ...mapActions(useAuthStore, ['getAuth', 'logout']),
        async handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                try {
                    await this.logout();
                    this.router.push('/login');
                } catch (error) {
                    console.error('Logout failed:', error);
                }
            }
        },
        handleAddClick() {
            window.dispatchEvent(new CustomEvent('nav-add-clicked'));
        }
    },
    created() {
        this.getAuth();
    },
};
</script>
