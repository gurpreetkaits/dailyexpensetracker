<template>
  <div class="mx-auto p-4 mb-10">
    <!-- Header with Add Button -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
      <div class="flex justify-between items-center">
        <div class="flex items-center gap-4">
          <button @click="showAddBankModal = true"
                  class="bg-emerald-500 text-white px-3 py-1.5 rounded-lg hover:bg-emerald-600 transition-colors items-center gap-1.5 text-sm flex">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Connect Bank
          </button>
          <span class="text-xs text-gray-500">(This feature is currently in progress)</span>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white rounded-xl shadow-sm p-4 overflow-x-auto min-h-[440px] relative">
      <!-- Loading State -->
      <div v-if="loading" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-10">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500"></div>
      </div>

      <template v-if="!loading">
        <!-- Bank Connection Options -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- Bank Connection Card -->
          <div v-for="bank in availableBanks" :key="bank.id"
               class="border border-gray-200 rounded-lg p-4 hover:border-emerald-500 transition-colors cursor-pointer"
               @click="selectBank(bank)">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                {{ getBankLogo(bank.provider) }}
              </div>
              <div>
                <h3 class="font-medium text-gray-900">{{ bank.name }}</h3>
                <p class="text-sm text-gray-500">{{ bank.description }}</p>
              </div>
            </div>
          </div>

          <!-- Add New Bank Connection -->
          <div class="border border-dashed border-gray-300 rounded-lg p-4 hover:border-emerald-500 transition-colors cursor-pointer flex items-center justify-center"
               @click="showAddBankModal = true">
            <div class="text-center">
              <div class="mx-auto h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </div>
              <h3 class="mt-2 font-medium text-gray-900">Add New Bank</h3>
              <p class="text-sm text-gray-500">Connect a new bank account</p>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="availableBanks.length === 0 && !loading" class="flex flex-col items-center justify-center min-h-[400px]">
          <Landmark class="h-10 w-10 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No bank connections</h3>
          <p class="mt-1 text-sm text-gray-500">Connect your bank accounts to automatically sync transactions.</p>
          <div class="mt-4">
            <button @click="showAddBankModal = true"
                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-emerald-500 hover:bg-emerald-600">
              Connect Bank
            </button>
          </div>
        </div>
      </template>
    </div>

    <!-- Add Bank Modal -->
    <GlobalModal
      v-model="showAddBankModal"
      title="Connect Bank Account"
    >
      <form @submit.prevent="connectBank" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Select Bank</label>
          <select
            v-model="bankForm.bankId"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 text-sm"
            required
          >
            <option value="" disabled selected>Select your bank</option>
            <option v-for="provider in bankProviders" :key="provider.id" :value="provider.id">
              {{ provider.name }} {{ provider.logo }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
          <input
            type="text"
            v-model="bankForm.name"
            placeholder="e.g. My Checking Account"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 text-sm"
            required
          />
        </div>

        <div class="bg-blue-50 p-3 rounded-md">
          <p class="text-sm text-blue-800">
            <span class="font-medium">Note:</span> You will be redirected to your bank's secure login page to authorize access.
          </p>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button type="button"
                  @click="showAddBankModal = false"
                  class="px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">
            Cancel
          </button>
          <button type="submit"
                  class="px-3 py-1.5 text-sm font-medium text-white bg-emerald-500 rounded-md hover:bg-emerald-600"
                  :disabled="isSubmitting">
            {{ isSubmitting ? 'Connecting...' : 'Connect' }}
          </button>
        </div>
      </form>
    </GlobalModal>

    <!-- Bank Details Modal -->
    <GlobalModal
      v-model="showBankDetailsModal"
      :title="selectedBank ? `${selectedBank.name} Details` : 'Bank Details'"
    >
      <div v-if="selectedBank" class="space-y-4">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
            {{ getBankLogo(selectedBank.provider) }}
          </div>
          <div>
            <h3 class="font-medium text-gray-900">{{ selectedBank.name }}</h3>
            <p class="text-sm text-gray-500">{{ selectedBank.description }}</p>
          </div>
        </div>

        <div class="border-t border-gray-200 pt-4">
          <h4 class="font-medium text-gray-900 mb-2">Connected Accounts</h4>
          <div v-if="selectedBank.accounts && selectedBank.accounts.length > 0" class="space-y-3">
            <div v-for="account in selectedBank.accounts" :key="account.id" class="bg-gray-50 p-3 rounded-md">
              <div class="flex justify-between">
                <div>
                  <p class="font-medium text-gray-900">{{ account.name }}</p>
                  <p class="text-sm text-gray-500">{{ account.accountType }} ({{ account.accountNumber }})</p>
                </div>
                <div class="text-right">
                  <p class="font-medium text-gray-900">{{ formatCurrency(account.balance) }}</p>
                  <p class="text-xs text-gray-500">Last updated: {{ formatDate(account.lastUpdated) }}</p>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-sm text-gray-500">
            No accounts connected yet.
          </div>
        </div>

        <div class="border-t border-gray-200 pt-4 flex justify-between">
          <button @click="refreshBankData(selectedBank)"
                  class="px-3 py-1.5 text-sm font-medium text-emerald-600 hover:bg-emerald-50 rounded-md flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh Data
          </button>
          <button @click="disconnectBank(selectedBank)"
                  class="px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 rounded-md flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Disconnect
          </button>
        </div>
      </div>
    </GlobalModal>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import GlobalModal from '../components/Global/GlobalModal.vue'
import { Landmark } from 'lucide-vue-next'
import { useNotifications } from '../composables/useNotifications'

export default {
  name: 'BankSyncView',
  components: {
    GlobalModal,
    Landmark
  },
  setup() {
    const { notify } = useNotifications()
    const loading = ref(false)
    const showAddBankModal = ref(false)
    const showBankDetailsModal = ref(false)
    const isSubmitting = ref(false)
    const selectedBank = ref(null)

    // Sample data - would be replaced with API calls
    const availableBanks = ref([])
    const bankProviders = ref([
      { id: 'chase', name: 'Chase Bank', logo: '🏦' },
      { id: 'bofa', name: 'Bank of America', logo: '🏛️' },
      { id: 'wells_fargo', name: 'Wells Fargo', logo: '🏦' },
      { id: 'citi', name: 'Citibank', logo: '🏛️' },
      { id: 'capital_one', name: 'Capital One', logo: '💳' },
      { id: 'amex', name: 'American Express', logo: '💳' },
      { id: 'discover', name: 'Discover', logo: '💳' },
      { id: 'other', name: 'Other Bank', logo: '🏦' }
    ])

    const bankForm = reactive({
      bankId: '',
      name: ''
    })

    const fetchBanks = async () => {
      loading.value = true
      try {
        // This would be an API call in a real implementation
        // For now, we'll use mock data
        await new Promise(resolve => setTimeout(resolve, 500))

        // Mock data with pre-populated banks
        availableBanks.value = [
          {
            id: '1',
            name: 'Chase Checking',
            type: 'bank',
            description: 'Connected to Chase Bank',
            provider: 'chase',
            lastSync: new Date(Date.now() - 24 * 60 * 60 * 1000), // 1 day ago
            accounts: [
              {
                id: 'acc_1',
                name: 'Primary Checking',
                accountType: 'Checking',
                accountNumber: '****4567',
                balance: 3245.67,
                availableBalance: 3145.67,
                currency: 'USD',
                lastUpdated: new Date(Date.now() - 24 * 60 * 60 * 1000) // 1 day ago
              },
              {
                id: 'acc_2',
                name: 'Savings Account',
                accountType: 'Savings',
                accountNumber: '****7890',
                balance: 12500.00,
                availableBalance: 12500.00,
                currency: 'USD',
                lastUpdated: new Date(Date.now() - 24 * 60 * 60 * 1000) // 1 day ago
              }
            ]
          },
          {
            id: '2',
            name: 'Bank of America',
            type: 'bank',
            description: 'Connected to Bank of America',
            provider: 'bofa',
            lastSync: new Date(Date.now() - 12 * 60 * 60 * 1000), // 12 hours ago
            accounts: [
              {
                id: 'acc_3',
                name: 'Credit Card',
                accountType: 'Credit',
                accountNumber: '****1234',
                balance: -1250.45,
                availableBalance: 3749.55, // Credit limit of 5000
                currency: 'USD',
                lastUpdated: new Date(Date.now() - 12 * 60 * 60 * 1000) // 12 hours ago
              }
            ]
          },
          {
            id: '3',
            name: 'Capital One',
            type: 'bank',
            description: 'Connected to Capital One',
            provider: 'capital_one',
            lastSync: new Date(Date.now() - 2 * 60 * 60 * 1000), // 2 hours ago
            accounts: [
              {
                id: 'acc_4',
                name: 'Venture Card',
                accountType: 'Credit',
                accountNumber: '****5678',
                balance: -2340.12,
                availableBalance: 7659.88, // Credit limit of 10000
                currency: 'USD',
                lastUpdated: new Date(Date.now() - 2 * 60 * 60 * 1000) // 2 hours ago
              },
              {
                id: 'acc_5',
                name: '360 Checking',
                accountType: 'Checking',
                accountNumber: '****9012',
                balance: 1875.43,
                availableBalance: 1875.43,
                currency: 'USD',
                lastUpdated: new Date(Date.now() - 2 * 60 * 60 * 1000) // 2 hours ago
              }
            ]
          }
        ]

      } catch (error) {
        console.error('Error fetching banks:', error)
        notify({ 
          title: 'Error',
          message: 'Failed to load bank connections',
          type: 'error'
        })
      } finally {
        loading.value = false
      }
    }

    const selectBank = (bank) => {
      selectedBank.value = bank
      showBankDetailsModal.value = true
    }

    const connectBank = async () => {
      isSubmitting.value = true
      try {
        // This would be an API call in a real implementation
        await new Promise(resolve => setTimeout(resolve, 1000))

        // Mock successful connection
        const selectedProvider = bankProviders.value.find(p => p.id === bankForm.bankId)

        const newBank = {
          id: Date.now().toString(),
          name: bankForm.name,
          type: 'bank',
          description: `Connected to ${selectedProvider.name}`,
          provider: bankForm.bankId,
          lastSync: new Date(),
          accounts: [
            {
              id: 'acc_' + Date.now(),
              name: bankForm.name,
              accountType: 'Checking',
              accountNumber: '****1234',
              balance: 1000,
              availableBalance: 1000,
              currency: 'USD',
              lastUpdated: new Date()
            }
          ]
        }

        availableBanks.value.push(newBank)
        showAddBankModal.value = false
        notify({
          title: 'Success',
          message: 'Bank connected successfully!',
          type: 'success'
        })

        // Reset form
        bankForm.bankId = ''
        bankForm.name = ''

      } catch (error) {
        console.error('Error connecting bank:', error)
        notify({
          title: 'Error',
          message: 'Failed to connect bank',
          type: 'error'
        })
      } finally {
        isSubmitting.value = false
      }
    }

    const refreshBankData = async (bank) => {
      try {
        notify({
          title: 'Info',
          message: 'Refreshing bank data...',
          type: 'info'
        })
        // This would be an API call in a real implementation
        await new Promise(resolve => setTimeout(resolve, 1000))

        // Update the last sync time for the bank
        const bankToUpdate = availableBanks.value.find(b => b.id === bank.id)
        if (bankToUpdate) {
          bankToUpdate.lastSync = new Date()

          // Update account balances with random changes to simulate new data
          bankToUpdate.accounts.forEach(account => {
            // Random balance change between -100 and 100
            const balanceChange = Math.round((Math.random() * 200 - 100) * 100) / 100
            account.balance += balanceChange
            account.availableBalance += balanceChange
            account.lastUpdated = new Date()
          })
        }

        notify({
          title: 'Success',
          message: 'Bank data refreshed successfully!',
          type: 'success'
        })
      } catch (error) {
        console.error('Error refreshing bank data:', error)
        notify({
          title: 'Error',
          message: 'Failed to refresh bank data',
          type: 'error'
        })
      }
    }

    const disconnectBank = async (bank) => {
      try {
        // This would be an API call in a real implementation
        await new Promise(resolve => setTimeout(resolve, 500))

        availableBanks.value = availableBanks.value.filter(b => b.id !== bank.id)
        showBankDetailsModal.value = false
        notify({
          title: 'Success',
          message: 'Bank disconnected successfully!',
          type: 'success'
        })
      } catch (error) {
        console.error('Error disconnecting bank:', error)
        notify({
          title: 'Error',
          message: 'Failed to disconnect bank',
          type: 'error'
        })
      }
    }

    const getBankIcon = (type) => {
      // For now, we'll just return the Landmark icon for all banks
      return Landmark
    }

    const getBankLogo = (providerId) => {
      const provider = bankProviders.value.find(p => p.id === providerId)
      return provider ? provider.logo : '🏦'
    }

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount)
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }

    onMounted(() => {
      fetchBanks()
    })

    return {
      loading,
      availableBanks,
      bankProviders,
      showAddBankModal,
      showBankDetailsModal,
      bankForm,
      isSubmitting,
      selectedBank,
      selectBank,
      connectBank,
      refreshBankData,
      disconnectBank,
      getBankIcon,
      getBankLogo,
      formatCurrency,
      formatDate
    }
  }
}
</script>
