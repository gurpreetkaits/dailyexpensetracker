<template>
  <div class="max-w-7xl mx-auto">
    <div class="space-y-4">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
      </div>

      <template v-else>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- General Settings -->
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

export default {
  name: 'AccountSettings',
  components: {
    Feedback
  },
  data() {
    return {
      isSaving: false,
      loading: false,
      currencies: [],
      selectedCurrency: '',
      reminder: false,
      income: 0,
      showResetDialog: false
    }
  },
  computed: {
    ...mapState(useSettingsStore, ['currency', 'billReminders', 'userIncome'])
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
    }
  },
  async created() {
    try {
      this.loading = true
      this.currencies = await fetchCurrencies()
      await this.fetchSettings()
      this.selectedCurrency = this.currency
      this.reminder = Boolean(this.billReminders)
      this.income = this.userIncome
    } catch (error) {
      console.log(error)
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