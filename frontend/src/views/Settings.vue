<!-- src/views/SettingsView.vue -->
<template>
  <div class="max-w-2xl mx-auto p-4 mb-10">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-4">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <template v-else>
      <!-- Show main settings menu only when not in a sub-route -->
      <template v-if="$route.path === '/settings'">
        <!-- Account Settings -->
        <div class="bg-white rounded-lg shadow mb-6">
          <div class="p-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Account</h2>
          </div>
          <div class="p-4">
            <div class="space-y-4">
              <router-link to="/settings/account" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-blue-100 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-medium text-gray-900">Account Settings</h3>
                    <p class="text-sm text-gray-500">Manage your preferences and feedback</p>
                  </div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Premium Features -->
        <div class="bg-white rounded-lg shadow mb-6">
          <div class="p-4 border-b">
            <h2 class="text-lg font-semibold text-emerald-600">Premium Features</h2>
          </div>
          <div class="p-4">
            <div class="space-y-4">
              <!-- Categories Management -->
              <router-link to="/categories" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-emerald-100 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-medium text-gray-900">Custom Categories</h3>
                    <p class="text-sm text-gray-500">Manage your custom expense categories</p>
                  </div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </router-link>

              <!-- Multi Wallet Management -->
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-emerald-100 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-medium text-gray-900">Multi Wallet</h3>
                    <p class="text-sm text-gray-500">Manage multiple wallets and accounts</p>
                    <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-800 rounded-full">Coming Soon</span>
                  </div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>

              <!-- Upgrade to Pro -->
              <router-link to="/plans" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-emerald-100 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-medium text-gray-900">Active Plans</h3>
                    <p class="text-sm text-gray-500">See all premium features</p>
                  </div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Sponsor Section -->
        <div class="bg-white rounded-lg shadow mb-6">
          <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Sponsor My Work</h2>
          </div>
          <div class="p-4 flex flex-col items-center">
            <p class="mb-3 text-gray-700 text-center">
              If this app saves you time or helps you manage your money, you can support its development!
            </p>
            <a href="https://buymeacoffee.com/gurpreetkash" target="_blank" rel="noopener"
               class="inline-block bg-yellow-400 hover:bg-yellow-500 text-white font-bold py-2 px-6 rounded-lg text-lg transition-colors">
              🍕 Buy Me a Pizza
            </a>
          </div>
        </div>
      </template>

      <!-- Router view for nested routes -->
      <router-view v-else></router-view>
    </template>
  </div>
</template>

<script>
import { ref } from 'vue'

export default {
  name: 'Settings',
  setup() {
    const loading = ref(false)
    return {
      loading
    }
  }
}
</script>
