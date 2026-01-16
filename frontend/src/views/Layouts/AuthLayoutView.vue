<template>
    <div class="h-screen bg-gray-100 overflow-y-hidden flex flex-col">
        <NotificationContainer />
        <ConfirmDialog />
        <Header />

        <div class="flex-1 overflow-y-auto">
            <!-- Main Content Area -->
            <main :class="showNav ? 'h-full bg-gray-100' : 'h-full flex items-center justify-center bg-gray-50'">
                <div v-if="showNav" class="h-full max-w-4xl mx-auto px-4">
                    <SubscriptionGuard>
                        <router-view />
                    </SubscriptionGuard>
                </div>
                <div v-else class="w-full">
                    <SubscriptionGuard>
                        <router-view />
                    </SubscriptionGuard>
                </div>
            </main>
        </div>

        <!-- Bottom Navigation (all screen sizes) -->
        <MobileNav v-if="showNav" />
    </div>
</template>

<script>
import { useAuthStore } from '../../store/auth'
import Header from '../../components/Layout/Header.vue'
import MobileNav from '../../components/Layout/MobileNav.vue'
import NotificationContainer from '../../components/Global/NotificationContainer.vue'
import ConfirmDialog from '../../components/Global/ConfirmDialog.vue'
import SubscriptionGuard from '../../components/SubscriptionGuard.vue'

export default {
    name: 'AuthLayoutView',
    components: {
        Header,
        MobileNav,
        NotificationContainer,
        ConfirmDialog,
        SubscriptionGuard
    },
    computed: {
        showNav() {
            return useAuthStore().token
        }
    }
}
</script>

<style>
main {
    padding-bottom: 5rem;
}
</style>
