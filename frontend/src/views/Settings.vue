<!-- src/views/SettingsView.vue -->
<template>
  <div class=" mx-auto py-8">
    <div class="flex flex-col md:flex-row gap-6">
      <!-- Tabs -->
      <div class="md:w-1/4 w-full flex md:flex-col flex-row gap-2 md:gap-4">
        <button
          class="w-full py-2.5 rounded-lg text-sm font-medium transition-all text-left md:text-base md:px-4 px-2"
          :class="selectedTab === 'general' ? 'bg-blue-50 text-blue-600' : 'bg-gray-50 text-gray-700 hover:bg-gray-100'"
          @click="selectedTab = 'general'"
        >
          General Settings
        </button>
        <button
          class="w-full py-2.5 rounded-lg text-sm font-medium transition-all text-left md:text-base md:px-4 px-2"
          :class="selectedTab === 'feedback' ? 'bg-blue-50 text-blue-600' : 'bg-gray-50 text-gray-700 hover:bg-gray-100'"
          @click="selectedTab = 'feedback'"
        >
          Feedback
        </button>
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
        <div v-else-if="selectedTab === 'feedback'">
          <div class="bg-white rounded-lg shadow hover:shadow-lg transition-all p-6">
            <div class="pb-4 border-b">
              <h2 class="text-xl font-semibold text-gray-800">Feedback</h2>
            </div>
            <div class="pt-6">
              <form @submit.prevent="handleSendFeedback">
                <div>
                  <textarea required placeholder="Let us know any feature requests or improvements!"
                    class="w-full rounded-lg bg-gray-50 border-none focus:ring-2 focus:ring-blue-500 p-3 text-base text-gray-800 transition-all resize-none min-h-[90px]"
                    v-model="feedback"></textarea>
                </div>
                <button type="submit"
                        :disabled="isSendingFeedback"
                        class="w-full py-2.5 rounded-lg text-sm font-medium transition-all flex items-center justify-center gap-2"
                        :class="isSendingFeedback ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-blue-50 text-blue-600 hover:bg-blue-100'">
                  <span v-if="isSendingFeedback"
                        class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                  {{ isSendingFeedback ? 'Sending...' : 'Send' }}
                </button>
              </form>
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
</template>

<script>
import { useSettingsStore } from '../store/settings'
import { storeFeedback, fetchCurrencies, resetUserTransactions } from '../services/SettingsService'
import { mapState, mapActions } from 'pinia'
import { ref } from 'vue'

export default {
  name: 'Settings',
  data() {
    return {
      selectedTab: 'general',
      isSaving: false,
      loading: false,
      currencies: [],
      selectedCurrency: '',
      showResetDialog: false,
      feedback: '',
      isSendingFeedback: false
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
    async handleSendFeedback() {
      if (!this.feedback) return
      this.isSendingFeedback = true
      try {
        await storeFeedback({ feedback: this.feedback })
        this.feedback = ''
      } catch (e) {
        console.log(e)
      } finally {
        this.isSendingFeedback = false
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
      console.log(error)
    } finally {
      this.loading = false
    }
  }
}
</script>
