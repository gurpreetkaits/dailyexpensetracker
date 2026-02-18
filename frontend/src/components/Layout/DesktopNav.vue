<template>
    <nav class="h-full p-4 flex flex-col justify-between">
        <!-- Logo and App Name -->
        <div>
            <a href="/overview" class="flex items-center gap-2 px-2 mb-4">
                <img src="../../assets/images/dailyexpensetracker.png" alt="Logo" class="h-8 w-8 object-contain rounded-lg" />
                <span class="font-semibold text-sm text-gray-800">Daily Expense Tracker</span>
            </a>

        <!-- Main Navigation -->
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

            <!-- Settings Dropdown -->
            <div class="mt-2 border-t border-gray-200 pt-2">
                <button
                    @click="showSettingsMenu = !showSettingsMenu"
                    class="flex items-center justify-between w-full px-3 py-2.5 rounded-xl transition-all duration-200"
                    :class="isSettingsActive
                        ? 'text-gray-900 bg-white shadow-sm'
                        : 'text-gray-500 hover:text-gray-700 hover:bg-white/50'"
                >
                    <div class="flex items-center gap-3">
                        <Settings class="w-5 h-5" :stroke-width="isSettingsActive ? 2.5 : 1.5" />
                        <span class="text-sm font-medium">Settings</span>
                    </div>
                    <ChevronDown class="w-4 h-4 transition-transform" :class="showSettingsMenu ? 'rotate-180' : ''" />
                </button>

                <!-- Settings Submenu -->
                <div v-if="showSettingsMenu" class="ml-4 mt-1 space-y-1">
                    <router-link
                        to="/settings/profile"
                        class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 text-sm"
                        :class="$route.path === '/settings/profile'
                            ? 'text-gray-900 bg-white shadow-sm'
                            : 'text-gray-500 hover:text-gray-700 hover:bg-white/50'"
                    >
                        <User class="w-4 h-4" />
                        <span>Profile</span>
                    </router-link>
                    <router-link
                        to="/settings/general"
                        class="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 text-sm"
                        :class="$route.path === '/settings/general'
                            ? 'text-gray-900 bg-white shadow-sm'
                            : 'text-gray-500 hover:text-gray-700 hover:bg-white/50'"
                    >
                        <SlidersHorizontal class="w-4 h-4" />
                        <span>General</span>
                    </router-link>
                </div>
            </div>
        </div>
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
                    <span>Ask</span>
                </a>

                <a
                    href="https://discord.com/users/983068355718180894"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-3 px-3 py-2 w-full rounded-xl hover:bg-indigo-50 transition-colors text-sm font-medium text-indigo-600 hover:text-indigo-700"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                    </svg>
                    <span>Ask</span>
                </a>

                <a
                    href="https://buymeacoffee.com/gurpreetkash"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-3 px-3 py-2 w-full rounded-xl hover:bg-orange-50 transition-colors text-sm font-medium text-orange-600 hover:text-orange-700"
                >
                    <Coffee class="w-4 h-4" />
                    <span>Support</span>
                </a>
            </div>
        </div>
    </nav>
</template>

<script>
import { Home, PieChart, MessageCircle, Wallet, Settings, Crown, LogOut, Coffee, Twitter, ChevronDown, SlidersHorizontal, LayoutGrid, User, ArrowLeftRight } from 'lucide-vue-next';
import { mapState, mapActions } from 'pinia';
import { useAuthStore } from '../../store/auth.js';
import { useRouter } from 'vue-router';
import { useConfirm } from '../../composables/useConfirm';

export default {
    name: 'DesktopNav',
    components: { Home, PieChart, MessageCircle, Wallet, Settings, Crown, LogOut, Coffee, Twitter, ChevronDown, SlidersHorizontal, LayoutGrid, User },
    data() {
        return {
            navigation: [
                { path: '/overview', name: 'Home', icon: Home },
                { path: '/stats', name: 'Stats', icon: PieChart },
                { path: '/chat', name: 'Chat', icon: MessageCircle },
                { path: '/wallets', name: 'Wallets', icon: Wallet },
                { path: '/transfers', name: 'Transfers', icon: ArrowLeftRight },
                { path: '/categories', name: 'Categories', icon: LayoutGrid },
                { path: '/plans', name: 'Plans', icon: Crown },
            ],
            showSettingsMenu: false,
        };
    },
    setup() {
        const router = useRouter();
        const { confirm } = useConfirm();
        return { router, confirm };
    },
    computed: {
        ...mapState(useAuthStore, ['user']),
        isSettingsActive() {
            return this.$route.path.startsWith('/settings');
        },
    },
    watch: {
        // Auto-expand settings menu if on settings page
        '$route.path': {
            immediate: true,
            handler(path) {
                if (path.startsWith('/settings')) {
                    this.showSettingsMenu = true;
                }
            }
        }
    },
    methods: {
        ...mapActions(useAuthStore, ['getAuth', 'logout']),
        async handleLogout() {
            const confirmed = await this.confirm({
                title: 'Logout',
                message: 'Are you sure you want to logout?',
                confirmText: 'Logout',
                cancelText: 'Cancel',
                type: 'warning'
            });

            if (confirmed) {
                try {
                    await this.logout();
                    this.router.push('/login');
                } catch (error) {
                    console.error('Logout failed:', error);
                }
            }
        },
    },
    created() {
        this.getAuth();
    },
};
</script>
