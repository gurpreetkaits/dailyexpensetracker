<!-- src/views/SettingsView.vue -->
<template>
  <div class="max-w-2xl mx-auto p-4">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-4">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <template v-else>
      <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b">
          <h2 class="text-lg font-semibold">General Settings</h2>
        </div>
        <div class="p-4 space-y-4">
          <!-- Currency Setting -->
          <div class="p-4 space-y-4">
            <!-- Currency Setting -->
            <div class="flex items-center justify-between">
              <div>
                <label class="block text-sm font-medium text-gray-700">Currency</label>
                <p class="text-sm text-gray-500">Select your preferred currency</p>
              </div>
              <div class="ml-4 w-full max-w-sm">
                <select v-model="selectedCurrency" @change="updateCurrency"
                  class="rounded-md border border-gray-300 p-2 w-full">
                  <option v-for="curr in currencies" :key="curr.code" :value="curr.code">
                    {{ curr.code }} - {{ curr.name }} ({{ curr.symbol }})
                  </option>
                </select>
              </div>
            </div>

            <!-- Minimum Monthly Income -->
            <div class="flex items-center justify-between">
              <div>
                <label class="block text-sm font-medium text-gray-700">Min Monthly Income</label>
              </div>
              <div class="ml-4 w-full max-w-sm">
                <input v-model="income" type="number" class="rounded-md border border-gray-300 p-2 w-full" required />
              </div>
            </div>
          </div>
        </div>
      </div>
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold ">Feedback</h2>
        </div>
        <div class="p-4">
            <p class="text-gray-600">I would love to hear your feedback. Please send me an email at <a
                href="mailto:gurpreetkait.codes@gmail.com">gurpreetkait.codes@gmail.com</a></p>
        </div>
    </div>
      <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b">
          <h2 class="text-lg font-semibold text-red-600">Danger Zone</h2>
        </div>
        <div class="p-4">
          <!-- Reset Button -->
          <button @click="showResetDialog = true"
                  class="bg-red-50  text-red-500 hover:bg-red-100 px-4 py-2 rounded-lg flex items-center gap-2">
            Reset All Transactions
          </button>

          <!-- Confirmation Dialog -->
          <div v-if="showResetDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg max-w-md">
              <h3 class="text-lg font-bold mb-2">Are you sure?</h3>
              <p class="text-gray-600 mb-4">This action cannot be undone. This will permanently delete all your transaction history.</p>
              <div class="flex justify-end gap-2">
                <button @click="showResetDialog = false"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                  Cancel
                </button>
                <button @click="handleReset"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                  Reset Transactions
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

<!--      <div class="bg-white rounded-lg shadow mb-6">-->
<!--        <div class="p-4 border-b">-->
<!--          <h2 class="text-lg font-semibold">Notifications</h2>-->
<!--        </div>-->

<!--        <div class="p-4 space-y-4">-->
<!--          <div class="flex items-center justify-between">-->
<!--            <div>-->
<!--              <label class="block text-sm font-medium text-gray-700">Bill Reminders</label>-->
<!--              <p class="text-sm text-gray-500">Remind me about upcoming bills</p>-->
<!--            </div>-->
<!--            <label class="relative inline-flex items-center cursor-pointer">-->
<!--              <input type="checkbox" v-model="reminder" class="sr-only peer">-->
<!--              <div-->
<!--                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">-->
<!--              </div>-->
<!--            </label>-->

<!--          </div>-->
<!--        </div>-->
<!--      </div>-->
      <div class="flex justify-start">
        <button @click="handleSaveSettings"
          class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center gap-2 disabled:opacity-50"
          :disabled="isSaving">
          <span v-if="isSaving"
            class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
          {{ isSaving ? 'Saving...' : 'Save' }}
        </button>

      </div>
      <div v-if="isStats">
        <DashboardView :key="stats[1]?.current_page" :stats="getStats" @load-stats="fetchStats" />
      </div>
    </template>
  </div>
</template>

<script>

import { mapActions, mapState } from 'pinia';
import {fetchCurrencies, resetUserTransactions} from '../services/SettingsService'
import { useSettingsStore } from '../store/settings';
import { LogOut, ChevronRight } from 'lucide-vue-next';
import DashboardView from './Admin/DashboardView.vue'

export default {
  name: 'Settings',
  data() {
    return {
      isSaving: false,
      loading: false,
      currencies: [],
      selectedCurrency: '',
      reminder: false,
      stats: '',
      showResetDialog: false,
      income: 0,
    }
  },
  watch: {
    stats: {
      immediate: true,
      deep: true,
      handler(newStats) {
        this.stats = newStats
      }
    }
  },
  components: {
    LogOut, DashboardView
  },
  computed: {
    ...mapState(useSettingsStore, ['currency', 'billReminders','userIncome']),
    isStats() {
      return this.stats[1]?.data?.length > 0
    },
    getStats() {
      return this.stats
    }
  },
  methods: {
    ...mapActions(useSettingsStore, ['addSettings', 'fetchSettings', 'loadStats']),
    async handleReset() {
      this.loading = true
      try {
        await resetUserTransactions();
        this.showResetDialog = false;
      } catch (error) {
        console.error(error);
      } finally {
        this.loading= false
      }
    },
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
    async fetchStats(page = 1) {
      this.stats = await this.loadStats(page)
    }
  },
  async created() {
    try {
      this.loading = true
      this.currencies = await fetchCurrencies()
        console.log(this.currencies)
      await this.fetchSettings()
      this.selectedCurrency = this.currency
      this.reminder = Boolean(this.billReminders)
      this.income = this.userIncome
      await this.fetchStats()
    } catch (error) {
      console.log(error)
    } finally {
      this.loading = false
    }
  },
}
</script>
