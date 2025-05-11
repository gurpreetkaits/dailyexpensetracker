<template>
    <nav class="h-full p-4 flex flex-col space-y-4">
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
import {Settings, Home, User, BarChart, MessageCircle, Star} from 'lucide-vue-next';
import {mapState, mapActions} from 'pinia';
import {useAuthStore} from '../../store/auth.js';

export default {
    name: 'DesktopNav',
    data() {
        return {
            navigation: [
                {path: '/dashboard', name: 'Dashboard', icon: Home, show: true},
                {path: '/overview', name: 'Overview', icon: Home, show: true},
                {path: '/stats', name: 'Stats', icon: BarChart, show: true},
                { path: '/chat', name: 'Chat', icon: MessageCircle, show: true },
                { path: '/plans', name: 'Plans', icon: Star, show: true },
                {path: '/settings', name: 'Settings', icon: Settings, show: true},
                {path: '/admin-dashboard', name: 'Dashboard', icon: User, show: true},
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
            const adminPaths = ['/admin-dashboard', '/feedbacks'];
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
