<template>
    <nav class="fixed left-20 top-1/3 transform -translate-y-1/4 bg-white shadow-lg p-4 rounded-r-xl hidden lg:flex flex-col gap-6">
        <router-link
            v-for="item in navigation"
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
import { Settings, Home, User, BarChart } from 'lucide-vue-next';

export default {
    name: 'DesktopNav',
    data() {
        return {
            navigation: [
                { path: '/overview', name: 'Home', icon: Home },
                { path: '/stats', name: 'Stats', icon: BarChart },
                { path: '/profile', name: 'Profile', icon: User },
                { path: '/settings', name: 'Settings', icon: Settings }
            ]
        };
    }
};
</script>
