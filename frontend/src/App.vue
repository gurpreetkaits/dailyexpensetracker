<template>
    <BaseLayout>
        <template  #sidebar>
            <DesktopNav v-if="showFooter" class="hidden sm:block" />
        </template>

        <template #header>
            <NotificationContainer />
            <Header />
        </template>

        <router-view />

        <template #mobile-nav>
            <MobileNav v-if="showFooter" />
        </template>
    </BaseLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from './store/auth'
import { useSettingsStore } from './store/settings'
import { verifyToken } from './services/AuthService'

import BaseLayout from './components/Layout/BaseLayout.vue'
import Header from './components/Layout/Header.vue'
import DesktopNav from './components/Layout/DesktopNav.vue'
import MobileNav from './components/Layout/MobileNav.vue'
import NotificationContainer from './components/Global/NotificationContainer.vue'

const auth    = useAuthStore()
const settings = useSettingsStore()
const route   = useRoute()
const router  = useRouter()

// Only show navs when logged in and not on the login route
const showFooter = computed(() =>
    Boolean(auth.token) && route.name !== 'login'
)

async function initApp() {
    if (!auth.token) return

    await settings.fetchSettings()
    try {
        const user = await verifyToken()
        auth.setAuth(auth.token, user)
    } catch {
        auth.clearAuth()
        router.push({ name: 'login' })
    }
}

initApp()
</script>
