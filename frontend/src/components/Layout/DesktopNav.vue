<template>
    <nav class="fixed left-1 top-1/3 transform -translate-y-1/4 bg-white shadow-lg p-4 rounded-r-xl hidden lg:flex flex-col gap-6">
        <router-link
            v-for="item in filteredNavigation"
            :key="item.path"
            :to="item.path"
            class="flex flex-col items-center mt-2"
            :class="{ 'text-blue-600': $route.path === item.path }"
        >
            <component :is="item.icon" />
            <span class="text-xs mt-1">{{ item.name }}</span>
        </router-link>
    </nav>
</template>

<script>
import { Settings, Home, User, BarChart, MessageCircle } from 'lucide-vue-next';
import {mapActions, mapState} from "pinia";
import {useAuthStore} from "../../store/auth.js";

export default {
    name: 'DesktopNav',
    data() {
        return {
            navigation: [
                { path: '/overview', name: 'Home', icon: Home, show: true },
                { path: '/stats', name: 'Stats', icon: BarChart, show: true },
                { path: '/chat', name: 'Chat', icon: MessageCircle, show: true },
                { path: '/settings', name: 'Settings', icon: Settings , show: true },
                { path: '/dashboard', name: 'Dashboard', icon: User, show: true },
                { path: '/feedbacks', name: 'Feedback', icon: User, show: true },
            ]
        };
    },
    computed: {
        ...mapState(useAuthStore, ['user']),
        isAdmin() {
            return this.user?.is_admin
        },
        filteredNavigation() {
            const adminPaths = ['/dashboard', '/feedbacks'];

            return this.navigation.filter(item =>
                this.isAdmin ||
                !adminPaths.includes(item.path)
            );
        }
    },
    methods: {
      ...mapActions(useAuthStore, ['getAuth']),
    },
    async created() {
        await this.getAuth()
    }
};
</script>
