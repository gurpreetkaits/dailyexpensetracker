<!-- src/views/SettingsView.vue -->
<template>
  <div class="max-w-7xl mx-auto relative">
    <div class="relative pb-24 px-3 pt-2">
      <!-- Settings Menu -->
      <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <!-- Mobile-only navigation items -->
        <div class="block md:hidden w-full border-b border-gray-100 pb-4 mb-4">
          <div class="flex flex-col gap-1.5">
            <button
              @click="navigateTo('overview2')"
              class="w-full py-3 px-4 text-sm font-medium rounded-xl transition-all text-left text-gray-600 hover:bg-gray-50 flex items-center justify-between"
            >
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                  <LayoutDashboard class="w-4 h-4 text-blue-500" />
                </div>
                <span>Overview</span>
              </div>
              <ChevronRight class="w-4 h-4 text-gray-300" />
            </button>
            <button
              @click="navigateTo('categories')"
              class="w-full py-3 px-4 text-sm font-medium rounded-xl transition-all text-left text-gray-600 hover:bg-gray-50 flex items-center justify-between"
            >
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                  <Tag class="w-4 h-4 text-purple-500" />
                </div>
                <span>Categories</span>
              </div>
              <ChevronRight class="w-4 h-4 text-gray-300" />
            </button>
            <button
              @click="navigateTo('wallets')"
              class="w-full py-3 px-4 text-sm font-medium rounded-xl transition-all text-left text-gray-600 hover:bg-gray-50 flex items-center justify-between"
            >
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                  <Wallet class="w-4 h-4 text-emerald-500" />
                </div>
                <span>Wallets</span>
              </div>
              <ChevronRight class="w-4 h-4 text-gray-300" />
            </button>
          </div>
        </div>

        <!-- Settings menu items -->
        <div class="flex flex-col gap-1.5">
          <button
            @click="navigateTo('general-settings')"
            class="w-full py-3 px-4 text-sm font-medium rounded-xl transition-all text-left text-gray-600 hover:bg-gray-50 flex items-center justify-between"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center">
                <SettingsIcon class="w-4 h-4 text-gray-400" />
              </div>
              <span>General</span>
            </div>
            <ChevronRight class="w-4 h-4 text-gray-300" />
          </button>
          <button
            @click="navigateTo('subscription')"
            class="w-full py-3 px-4 text-sm font-medium rounded-xl transition-all text-left text-gray-600 hover:bg-gray-50 flex items-center justify-between"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                <Crown class="w-4 h-4 text-amber-400" />
              </div>
              <span>Subscription</span>
            </div>
            <ChevronRight class="w-4 h-4 text-gray-300" />
          </button>
        </div>

        <!-- Logout -->
        <div class="border-t border-gray-100 mt-4 pt-4">
          <button
            @click="handleLogout"
            class="w-full py-3 px-4 text-sm font-medium rounded-xl transition-all text-left text-red-600 hover:bg-red-50 flex items-center justify-between"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                <LogOut class="w-4 h-4 text-red-500" />
              </div>
              <span>Logout</span>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { LayoutDashboard, Tag, Wallet, Settings as SettingsIcon, Crown, ChevronRight, LogOut } from 'lucide-vue-next'
import { useAuthStore } from '../store/auth'
import { useConfirm } from '../composables/useConfirm'
import { mapActions } from 'pinia'

export default {
  name: 'SettingsView',
  components: {
    LayoutDashboard,
    Tag,
    Wallet,
    SettingsIcon,
    Crown,
    ChevronRight,
    LogOut
  },
  setup() {
    const { confirm } = useConfirm()
    return { confirm }
  },
  methods: {
    ...mapActions(useAuthStore, ['logout']),
    navigateTo(routeName) {
      this.$router.push({ name: routeName })
    },
    async handleLogout() {
      const confirmed = await this.confirm({
        title: 'Logout',
        message: 'Are you sure you want to logout?',
        confirmText: 'Logout',
        cancelText: 'Cancel',
        type: 'danger'
      })

      if (confirmed) {
        try {
          await this.logout()
          this.$router.push('/login')
        } catch (error) {
          console.error('Logout failed:', error)
        }
      }
    }
  }
}
</script>
