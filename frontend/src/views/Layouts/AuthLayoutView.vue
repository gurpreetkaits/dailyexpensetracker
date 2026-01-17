<template>
    <div class="h-screen bg-gray-100 overflow-y-hidden flex flex-col">
        <NotificationContainer />
        <ConfirmDialog />

        <!-- Header only on mobile -->
        <Header class="md:hidden" />

        <div class="flex-1 overflow-y-hidden flex">
            <!-- Desktop Sidebar -->
            <aside v-if="showNav" class="hidden md:block w-64 bg-white border-r border-gray-200 flex-shrink-0">
                <DesktopNav />
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 overflow-y-auto">
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
        </div>

        <!-- Bottom Navigation (mobile only) -->
        <MobileNav v-if="showNav" class="md:hidden" />
    </div>
</template>

<script>
import { useAuthStore } from '../../store/auth'
import Header from '../../components/Layout/Header.vue'
import MobileNav from '../../components/Layout/MobileNav.vue'
import DesktopNav from '../../components/Layout/DesktopNav.vue'
import NotificationContainer from '../../components/Global/NotificationContainer.vue'
import ConfirmDialog from '../../components/Global/ConfirmDialog.vue'
import SubscriptionGuard from '../../components/SubscriptionGuard.vue'

export default {
    name: 'AuthLayoutView',
    components: {
        Header,
        MobileNav,
        DesktopNav,
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
/* Mobile: add bottom padding for bottom nav */
main {
    padding-bottom: 5rem;
}

/* Desktop: no bottom padding needed since nav is on side */
@media (min-width: 768px) {
    main {
        padding-bottom: 0;
    }
}
</style>
