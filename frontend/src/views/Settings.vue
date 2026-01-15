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
      </div>
    </div>
  </div>
</template>

<script>
import { LayoutDashboard, Tag, Wallet, Settings as SettingsIcon, Crown, ChevronRight } from 'lucide-vue-next'

export default {
  name: 'SettingsView',
  components: {
    LayoutDashboard,
    Tag,
    Wallet,
    SettingsIcon,
    Crown,
    ChevronRight
  },
  methods: {
    navigateTo(routeName) {
      this.$router.push({ name: routeName })
    }
  }
}
</script>
