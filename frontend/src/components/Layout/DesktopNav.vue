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
                <span>{{ item.name }} <span v-if="item?.beta" class="text-xs text-emerald-500">(beta)</span> <span v-if="item?.premium" class="text-xs text-emerald-500">(premium)</span></span>
            </router-link>
        </div>
        <div>
            <button
                @click="handleLogout"
                class="flex items-center p-2 w-full rounded-md hover:bg-red-100 transition-colors text-sm font-medium text-red-600 hover:text-red-700"
            >
                <LogOut class="w-5 h-5 mr-3 flex-shrink-0" />
                <span>Logout</span>
            </button>
        </div>
    </nav>
</template>

<script>
import { Settings, Home, User, BarChart, MessageCircle, Star, LogOut } from 'lucide-vue-next';
import { mapState, mapActions } from 'pinia';
import { useAuthStore } from '../../store/auth.js';
import { useRouter } from 'vue-router';

export default {
    name: 'DesktopNav',
    components: { LogOut }, 
    data() {
        return {
            navigation: [
                { path: '/overview', name: 'Overview', icon: Home, show: true },
                { path: '/stats', name: 'Stats', icon: BarChart, show: true },
                { path: '/chat', name: 'Chat', icon: MessageCircle, show: true,  premium: true},
                { path: '/plans', name: 'Plans', icon: Star, show: true, beta: true },
                { path: '/settings', name: 'Settings', icon: Settings, show: true },
                { path: '/admin-dashboard', name: 'Dashboard', icon: User, show: true }, // Example admin route
                { path: '/feedbacks', name: 'Feedback', icon: User, show: true }, // Example admin route
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
    },
    created() {
        this.getAuth(); // Ensure user state is loaded
    },
};
</script>

<style scoped>
/* Adjusted styles for better spacing and logout button positioning */
</style>
