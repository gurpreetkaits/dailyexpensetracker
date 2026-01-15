<template>
  <div class="max-w-7xl mx-auto relative">
    <div class="relative pb-24 px-3 pt-2">
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
</template>

<script>
import { useSettingsStore } from '../../store/settings'
import { fetchCurrencies, resetUserTransactions } from '../../services/SettingsService'
import { mapState, mapActions } from 'pinia'
import { useNotifications } from '../../composables/useNotifications'

export default {
  name: 'GeneralSettingsView',
  setup() {
    const { notify } = useNotifications()
    return { notify }
  },
  data() {
    return {
      isSaving: false,
      loading: false,
      currencies: [],
      selectedCurrency: '',
      showResetDialog: false
    }
  },
  computed: {
    ...mapState(useSettingsStore, ['currency', 'currencyCode'])
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
        this.notify({
          title: 'Success',
          message: 'Settings saved successfully',
          type: 'success'
        })
      } catch (e) {
        console.log(e)
        this.notify({
          title: 'Error',
          message: 'Failed to save settings',
          type: 'error'
        })
      } finally {
        this.isSaving = false
      }
    },
    async handleReset() {
      this.loading = true
      try {
        await resetUserTransactions()
        this.showResetDialog = false
        this.notify({
          title: 'Success',
          message: 'All transactions have been reset',
          type: 'success'
        })
      } catch (error) {
        console.error(error)
        this.notify({
          title: 'Error',
          message: 'Failed to reset transactions',
          type: 'error'
        })
      } finally {
        this.loading = false
      }
    }
  },
  async created() {
    try {
      this.loading = true
      this.currencies = await fetchCurrencies()
      await this.fetchSettings()
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
