<template>
    <nav class="h-full p-4 flex flex-col justify-between">
        <div class="space-y-1">
            <router-link
                v-for="item in filteredNavigation"
                :key="item.path"
                :to="item.path"
                class="flex items-center p-2 rounded-md hover:text-emerald-600 transition-colors text-sm font-medium"
                :class="$route.path === item.path ? 'bg-emerald-50 text-emerald-600' : 'text-gray-700 hover:text-gray-900'"
            >
                <component :is="item.icon" class="w-5 h-5 mr-3 flex-shrink-0 " :class="$route.path === item.path ? 'bg-emerald-50 text-emerald-600' : 'hover:bg-emerald-50 hover:text-emerald-600'" />
                <span>{{ item.name }} <span v-if="item?.beta" class="text-xs text-emerald-500">(beta)</span>
                <!-- <span v-if="item?.premium" class="items-center text-[6px] bg-blue-100 text-blue-600 px-1 py-0.5 rounded-full">PRO</span> -->
                </span>
            </router-link>
        </div>

        <div class="space-y-4">
            <button
                @click="handleLogout"
                class="flex items-center p-2 w-full rounded-md hover:bg-red-100 transition-colors text-sm font-medium text-red-600 hover:text-red-700"
            >
                <LogOut class="w-5 h-5 mr-3 flex-shrink-0" />
                <span>Logout</span>
            </button>

            <!-- Creator Section -->
            <div class="border-t border-gray-200 pt-4">
                <div class="text-xs text-gray-500 mb-3 font-medium">Creator</div>
                
                <a 
                    href="https://x.com/gurpreetkait" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    class="flex items-center p-2 w-full rounded-md hover:bg-gray-50 transition-colors text-sm font-medium text-gray-700 hover:text-gray-900 mb-2"
                >
                    <Twitter class="w-5 h-5 mr-3 flex-shrink-0 text-blue-400" />
                    <span>@gurpreetkait</span>
                </a>
                
                <a 
                    href="https://buymeacoffee.com/gurpreetkait" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    class="flex items-center p-2 w-full rounded-md hover:bg-orange-50 transition-colors text-sm font-medium text-orange-600 hover:text-orange-700"
                >
                    <Coffee class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span>Buy me a coffee</span>
                </a>
            </div>
        </div>
    </nav>
</template>

<script>
import { Settings, Home, User, BarChart, MessageCircle, Star, LogOut, MessageCircleCode, Wallet, Landmark, Coffee, Twitter } from 'lucide-vue-next';
import { mapState, mapActions } from 'pinia';
import { useAuthStore } from '../../store/auth.js';
import { useRouter } from 'vue-router';

export default {
    name: 'DesktopNav',
    components: { LogOut, MessageCircle, Wallet, Landmark, Coffee, Twitter },
    data() {
        return {
            navigation: [
                { path: '/overview', name: 'Overview', icon: Home, show: true },
                { path: '/overview2', name: 'Overview2.0', icon: Home, show: true },
                { path: '/stats', name: 'Stats', icon: BarChart, show: true },
                { path: '/chat', name: 'Chat', icon: MessageCircle, show: true, premium: true },
                { path: '/wallets', name: 'Wallets', icon: Wallet, show: true },
                { path: '/bank-sync', name: 'Bank Sync', icon: Landmark, show: true, beta: true },
                { path: '/categories', name: 'Categories', icon: Settings, show: true, beta: false },
                { path: '/settings/account', name: 'Account', icon: User, show: true, beta: false },
                { path: '/admin-dashboard', name: 'Dashboard', icon: User, show: true },
                { path: '/feedbacks', name: 'Feedback', icon: User, show: true },
                // { path: '/plansgs', name: 'Plans', icon: User, show: true },
            ],
        };
    },
    setup() {
        const router = useRouter();
        return { router };
    },
    computed: {
        ...mapState(useAuthStore, ['user']),
        isAdmin() {
            return this.user?.is_admin;
        },
        filteredNavigation() {
            const adminPaths = ['/admin-dashboard', '/feedbacks'];
            return this.navigation.filter(
                item => item.show && (this.isAdmin || !adminPaths.includes(item.path))
            );
        },
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
                    // Optionally, show a notification to the user
                }
            }
        },
        handleSupportClick() {
            window.location.href = 'mailto:gurpreetkait.codes@gmail.com'
        }
    },
    created() {
        this.getAuth(); // Ensure user state is loaded
    },
};
</script>

<style scoped>
/* Adjusted styles for better spacing and logout button positioning */
</style>
