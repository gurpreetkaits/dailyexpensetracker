<template>
    <nav class="fixed left-0 top-16 h-full w-48 bg-white shadow p-4 flex flex-col space-y-4">
        <router-link
            v-for="item in filteredNavigation"
            :key="item.path"
            :to="item.path"
            class="flex items-center p-2 rounded hover:bg-gray-100 transition-colors"
            :class="{ 'text-blue-600': $route.path === item.path }"
        >
            <component :is="item.icon" class="w-6 h-6 flex-shrink-0" />
            <span class="ml-2 text-sm font-medium">{{ item.name }}</span>
        </router-link>
    </nav>
</template>

<script>
import { Settings, Home, User, BarChart } from 'lucide-vue-next';
import { mapState, mapActions } from 'pinia';
import { useAuthStore } from '../../store/auth.js';

export default {
    name: 'DesktopNav',
    data() {
        return {
            navigation: [
                {path: '/overview', name: 'Home', icon: Home, show: true},
                {path: '/stats', name: 'Stats', icon: BarChart, show: true},
                {path: '/categories', name: 'Categories', icon: Settings, show: true},
                {path: '/settings', name: 'Settings', icon: Settings, show: true},
                {path: '/profile', name: 'Profile', icon: User, show: true},
                {path: '/dashboard', name: 'Dashboard', icon: User, show: true},
                {path: '/feedbacks', name: 'Feedback', icon: User, show: true},
            ],
        };
    },
    computed: {
        ...mapState(useAuthStore, ['user']),
        isAdmin() {
            return this.user?.is_admin;
        },
        filteredNavigation() {
            const adminPaths = ['/dashboard', '/feedbacks'];
            return this.navigation.filter(
                item => item.show && (this.isAdmin || !adminPaths.includes(item.path))
            );
        },
    },
    methods: {
        ...mapActions(useAuthStore, ['getAuth']),
    },
    created() {
        this.getAuth();
    },
};
</script>

<style scoped>
/* Static sidebar: always show icons and labels */
</style>
