<template>
  <div class="max-w-7xl mx-auto">
    <div class="space-y-4">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
      </div>

      <template v-else>
        <div class="flex gap-6">
          <!-- Left Sidebar - Tabs -->
          <div class="w-64 flex-shrink-0">
            <div class="bg-white rounded-lg shadow">
              <div class="p-4 border-b">
                <h2 class="text-lg font-semibold text-gray-800">Settings</h2>
              </div>
              <div class="p-2">
                <!-- Mobile-only navigation items -->
                <div class="block sm:hidden border-b border-gray-200 pb-2 mb-2">
                  <button 
                    @click="navigateTo('overview2')"
                    class="w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors flex items-center gap-3 text-gray-600 hover:bg-gray-50"
                  >
                    <span class="w-5 h-5">📊</span>
                    Overview2.0
                  </button>
                  <button 
                    @click="navigateTo('categories')"
                    class="w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors flex items-center gap-3 text-gray-600 hover:bg-gray-50"
                  >
                    <span class="w-5 h-5">🏷️</span>
                    Categories
                  </button>
                  <button 
                    @click="navigateTo('wallets')"
                    class="w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors flex items-center gap-3 text-gray-600 hover:bg-gray-50"
                  >
                    <span class="w-5 h-5">💳</span>
                    Wallets
                  </button>
                </div>
                
                <!-- Regular settings tabs -->
                <button 
                  @click="activeTab = 'general'"
                  class="w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors flex items-center gap-3"
                  :class="activeTab === 'general' ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50'"
                >
                  <span class="w-5 h-5">⚙️</span>
                  General
                </button>
                <button 
                  @click="activeTab = 'subscription'"
                  class="w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors flex items-center gap-3"
                  :class="activeTab === 'subscription' ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50'"
                >
                  <span class="w-5 h-5">⭐</span>
                  Subscription
                </button>
              </div>
            </div>
          </div>

          <!-- Right Content -->
          <div class="flex-1">
            <!-- General Settings Tab -->
            <div v-if="activeTab === 'general'" class="space-y-6">
              <!-- General Settings Card -->
              <div class="bg-white rounded-lg shadow hover:shadow-lg transition-all p-6">
                <div class="p-6 border-b">
                  <h2 class="text-xl font-semibold text-gray-800">General Settings</h2>
                </div>
                <div class="p-6 space-y-6">
                  <!-- Currency Setting -->
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Currency</label>
                    <p class="text-sm text-gray-500">Select your preferred currency</p>
                    <select v-model="selectedCurrency"
                            class="mt-1 rounded-lg border border-gray-200 bg-gray-50 p-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base transition-all">
                      <option v-for="curr in currencies" :key="curr.code" :value="curr.code">
                        {{ curr.code }} - {{ curr.name }} ({{ curr.symbol }})
                      </option>
                    </select>
                  </div>

                  <button @click="handleSaveSettings"
                          :disabled="isSaving"
                          class="w-full py-2.5 rounded-lg text-sm font-medium transition-all flex items-center justify-center gap-2"
                          :class="isSaving ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-blue-50 text-blue-600 hover:bg-blue-100'">
                    <span v-if="isSaving"
                          class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    {{ isSaving ? 'Saving...' : 'Save Changes' }}
                  </button>
                </div>
              </div>

              <!-- Feedback Section -->
              <div class="bg-white rounded-lg shadow hover:shadow-lg transition-all p-6">
                <div class="p-6 border-b">
                  <h2 class="text-xl font-semibold text-gray-800">Feedback</h2>
                </div>
                <div class="p-6">
                  <Feedback />
                </div>
              </div>

              <!-- Danger Zone -->
              <div class="bg-white rounded-lg shadow hover:shadow-lg transition-all p-6">
                <div class="p-6 border-b">
                  <h2 class="text-xl font-semibold text-red-600">Danger Zone</h2>
                </div>
                <div class="p-6 space-y-4">
                  <button @click="showResetDialog = true"
                          class="w-full py-2.5 rounded-lg text-sm font-medium transition-all bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center gap-2">
                    Reset All Transactions
                  </button>
                  <button @click="handleLogout"
                          class="w-full py-2.5 rounded-lg text-sm font-medium transition-all bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center gap-2">
                    <span>Log Out</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Subscription Tab -->
            <div v-if="activeTab === 'subscription'" class="space-y-6">
              <div class="bg-white rounded-lg shadow hover:shadow-lg transition-all p-6">
                <div class="flex items-center justify-between mb-6">
                  <div>
                    <h2 class="text-xl font-semibold text-gray-800">Subscription Status</h2>
                    <p class="text-sm text-gray-600 mt-1">Your current plan details</p>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-sm font-medium"
                      :class="hasActiveSubscription ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'">
                      {{ hasActiveSubscription ? 'Pro Plan Active' : 'No Active Plan' }}
                    </span>
                  </div>
                </div>

                <!-- Plan Details -->
                <div v-if="hasActiveSubscription && polarStore.subscription" class="rounded-xl border p-6 bg-blue-50 border-blue-500 ring-1 ring-blue-500">
                  <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-xl">⭐</span>
                      </div>
                      <div>
                        <h3 class="text-lg font-semibold text-gray-900">Pro Plan</h3>
                        <p class="text-sm text-gray-600">{{ polarStore.subscription.interval || 'monthly' }} billing</p>
                      </div>
                    </div>
                    <div class="text-right">
                      <p class="text-2xl font-bold text-gray-900">{{ formatPrice(polarStore.subscription.amount / 100 || 0) }}</p>
                      <p class="text-sm text-gray-600">per {{ polarStore.subscription.interval || 'month' }}</p>
                    </div>
                  </div>

                  <div class="mt-6 pt-6 border-t border-blue-200">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                      <div>
                        <p class="text-gray-600">Start Date</p>
                        <p class="font-medium text-gray-900">
                          {{ polarStore.subscription.created_at ? new Date(polarStore.subscription.created_at).toLocaleDateString() : '-' }}
                        </p>
                      </div>
                      <div>
                        <p class="text-gray-600">Next Billing</p>
                        <p class="font-medium text-gray-900">
                          {{ polarStore.subscription.current_period_end ? new Date(polarStore.subscription.current_period_end).toLocaleDateString() : '-' }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <div class="mt-6">
                    <button @click="handleManageSubscription"
                      class="w-full rounded-lg border border-blue-600 px-4 py-2.5 text-sm font-semibold text-blue-600 hover:bg-blue-50 transition-colors">
                      Manage Subscription
                    </button>
                  </div>
                </div>

                <div v-else class="rounded-xl border p-6 bg-gray-50 border-gray-200 text-center">
                  <p class="text-gray-600">No active subscription found</p>
                  <p class="text-sm text-gray-500 mt-1">Please contact support if you believe this is an error</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Reset Dialog -->
        <div v-if="showResetDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div class="bg-white p-8 rounded-lg max-w-md w-full mx-4">
            <h3 class="text-xl font-bold mb-3 text-gray-900">Are you sure?</h3>
            <p class="text-gray-600 mb-6">This action cannot be undone. This will permanently delete all your transaction history.</p>
            <div class="flex justify-end gap-3">
              <button @click="showResetDialog = false"
                      class="px-4 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                Cancel
              </button>
              <button @click="handleReset"
                      class="px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                Reset Transactions
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { fetchCurrencies, resetUserTransactions } from '../../services/SettingsService'
import { useSettingsStore } from '../../store/settings'
import { useAuthStore } from '../../store/auth'
import Feedback from '../../components/Settings/Feedback.vue'
import { usePolarStore } from '../../store/polar'
import { useCurrency } from '../../composables/useCurrency'
import { useNotifications } from '../../composables/useNotifications'

export default {
  name: 'AccountSettings',
  components: {
    Feedback
  },
  setup() {
    const polarStore = usePolarStore()
    const { currency } = useCurrency()
    const { notify } = useNotifications()
    return { polarStore, currency, notify }
  },
  data() {
    return {
      isSaving: false,
      loading: false,
      currencies: [],
      selectedCurrency: '',
      reminder: false,
      income: 0,
      showResetDialog: false,
      activeTab: 'general'
    }
  },
  computed: {
    ...mapState(useSettingsStore, ['currency', 'billReminders', 'userIncome']),
    hasActiveSubscription() {
      return this.polarStore.hasActiveSubscription
    },
    formatPrice() {
      return (price) => {
        return new Intl.NumberFormat(this.currency.value, { 
          style: 'currency', 
          currency: this.currency.value,
          minimumFractionDigits: this.currency.value === 'INR' ? 0 : 2
        }).format(price)
      }
    }
  },
  methods: {
    ...mapActions(useSettingsStore, ['addSettings', 'fetchSettings']),
    ...mapActions(useAuthStore, ['clearAuth']),
    async handleSaveSettings() {
      try {
        this.isSaving = true
        let params = {
          reminders: this.reminder,
          currency_code: this.selectedCurrency,
          income: this.income
        }
        await this.addSettings(params)
      } catch (e) {
        console.log(e)
      } finally {
        this.isSaving = false
      }
    },
    async handleReset() {
      this.loading = true
      try {
        await resetUserTransactions()
        this.showResetDialog = false
      } catch (error) {
        console.error(error)
      } finally {
        this.loading = false
      }
    },
    handleLogout() {
      if (!confirm('Are you sure you want to logout?')) {
        return
      }
      try {
        this.clearAuth()
        this.$router.push('/login')
      } catch (error) {
        console.error('Logout failed:', error)
      }
    },
    handleManageSubscription() {
      this.notify({
        title: 'Coming Soon',
        message: 'Subscription management portal will be available soon',
        type: 'info'
      })
    },
    navigateTo(routeName) {
      this.$router.push({ name: routeName })
    }
  },
  async created() {
    try {
      this.loading = true
      this.currencies = await fetchCurrencies()
      await this.fetchSettings()
      await this.polarStore.fetchSubscriptionStatus()
      this.selectedCurrency = this.currency
      this.reminder = Boolean(this.billReminders)
      this.income = this.userIncome
    } catch (error) {
      console.error('Error in created:', error)
      this.notify({
        title: 'Error',
        message: 'Failed to load settings',
        type: 'error'
      })
    } finally {
      this.loading = false
    }
  }
}
</script>

<style scoped>
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.hover\:shadow-lg:hover {
  box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
}
</style> 