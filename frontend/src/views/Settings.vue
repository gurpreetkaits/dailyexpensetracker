<!-- src/views/SettingsView.vue -->
<template>
  <div class="max-w-7xl mx-auto relative">
    <div class="relative pb-24 px-3 pt-2">
      <div class="flex flex-col md:flex-row gap-6">
      <!-- Menu Items -->
      <div class="md:w-1/4 w-full bg-white p-4 rounded-xl shadow-sm border border-gray-100">
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
            class="w-full py-3 px-4 text-sm font-medium rounded-xl transition-all text-left flex items-center justify-between"
            :class="selectedTab === 'general' ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50'"
            @click="selectedTab = 'general'"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="selectedTab === 'general' ? 'bg-gray-200' : 'bg-gray-50'">
                <SettingsIcon class="w-4 h-4" :class="selectedTab === 'general' ? 'text-gray-700' : 'text-gray-400'" />
              </div>
              <span>General</span>
            </div>
            <ChevronRight class="w-4 h-4 text-gray-300" />
          </button>
          <button
            class="w-full py-3 px-4 text-sm font-medium rounded-xl transition-all text-left flex items-center justify-between"
            :class="selectedTab === 'subscription' ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50'"
            @click="selectedTab = 'subscription'"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="selectedTab === 'subscription' ? 'bg-amber-100' : 'bg-amber-50'">
                <Crown class="w-4 h-4" :class="selectedTab === 'subscription' ? 'text-amber-600' : 'text-amber-400'" />
              </div>
              <span>Subscription</span>
            </div>
            <ChevronRight class="w-4 h-4 text-gray-300" />
          </button>
        </div>
      </div>
      <!-- Tab Content -->
      <div class="flex-1">
        <div v-if="selectedTab === 'general'">
          <!-- Currency Card -->
          <div class="bg-white rounded-lg shadow hover:shadow-lg transition-all p-6 mb-6">
            <div class="pb-4 border-b">
              <h2 class="text-xl font-semibold text-gray-800">General Settings</h2>
            </div>
            <div class="pt-6 space-y-6">
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
          <!-- Danger Zone -->
          <div class="bg-white rounded-lg shadow hover:shadow-lg transition-all p-6">
            <div class="pb-4 border-b">
              <h2 class="text-xl font-semibold text-red-600">Danger Zone</h2>
            </div>
            <div class="pt-6">
              <button @click="showResetDialog = true"
                      class="w-full py-2.5 rounded-lg text-sm font-medium transition-all bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center gap-2">
                Reset All Transactions
              </button>
            </div>
          </div>
        </div>
        <div v-else-if="selectedTab === 'subscription'">
          <div class="bg-white rounded-lg shadow hover:shadow-lg transition-all p-6">
            <div class="pb-4 border-b">
              <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Subscription Status</h2>
                <span class="px-3 py-1 rounded-full text-sm font-medium"
                  :class="hasActiveSubscription ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'">
                  {{ hasActiveSubscription ? 'Pro Plan Active' : 'No Active Plan' }}
                </span>
              </div>
            </div>
            <div class="pt-6">
              <div v-if="hasActiveSubscription && polarStore.subscription" class="rounded-xl border p-6 bg-blue-50 border-blue-500 ring-1 ring-blue-500">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                      <Crown class="w-5 h-5 text-amber-600" />
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
      </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useSettingsStore } from '../store/settings'
import { fetchCurrencies, resetUserTransactions } from '../services/SettingsService'
import { mapState, mapActions } from 'pinia'
import { ref } from 'vue'
import { usePolarStore } from '../store/polar'
import { useCurrency } from '../composables/useCurrency'
import { useNotifications } from '../composables/useNotifications'
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
  setup() {
    const polarStore = usePolarStore()
    const { currency } = useCurrency()
    const { notify } = useNotifications()
    return { polarStore, currency, notify }
  },
  data() {
    return {
      selectedTab: 'general',
      isSaving: false,
      loading: false,
      currencies: [],
      selectedCurrency: '',
      showResetDialog: false
    }
  },
  computed: {
    ...mapState(useSettingsStore, ['currency', 'currencyCode']),
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
    async handleSaveSettings() {
      try {
        this.isSaving = true
        let params = {
          currency_code: this.selectedCurrency,
          reminders: false
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
      this.selectedCurrency = this.currencyCode || this.currency || (this.currencies[0] && this.currencies[0].code) || ''
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
