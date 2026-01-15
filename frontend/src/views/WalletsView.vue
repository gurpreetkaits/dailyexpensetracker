<template>
  <div class="max-w-7xl mx-auto relative">
    <div class="relative pb-24 px-3 pt-2">
    <!-- Header with Add Button and Pagination -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
      <div class="flex justify-between items-center">
        <div class="flex items-center gap-4">
          <button @click="showAddModal = true"
                  class="bg-emerald-500 text-white px-3 py-1.5 rounded-lg hover:bg-emerald-600 transition-colors items-center gap-1.5 text-sm flex">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Wallet
          </button>
          <button @click="showTransferModal = true"
                  class="bg-blue-500 text-white px-3 py-1.5 rounded-lg hover:bg-blue-600 transition-colors items-center gap-1.5 text-sm flex">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            Transfer
          </button>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex items-center gap-4">
          <div class="text-sm text-gray-500">
            Showing {{ startIndex }}-{{ endIndex }} of {{ totalItems }}
          </div>
          <div class="flex items-center gap-2">
            <button v-for="link in paginationLinks"
                    :key="link.label"
                    @click="handlePageChange(link.url)"
                    :disabled="!link.url"
                    class="px-3 py-1.5 rounded-lg border text-sm font-medium transition-colors disabled:opacity-50"
                    :class="[
                      link.active
                        ? 'bg-emerald-500 text-white border-emerald-500'
                        : 'text-gray-700 border-gray-300 hover:bg-gray-50',
                      !link.url ? 'text-gray-400 border-gray-200' : ''
                    ]"
                    v-html="link.label">
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Wallets List -->
    <div class="bg-white rounded-xl shadow-sm p-4 overflow-x-auto min-h-[440px] relative">
      <!-- Loading State -->
      <div v-if="loading" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-10">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500"></div>
      </div>

      <template v-if="!loading">
        <!-- Mobile View -->
        <div v-if="wallets.length > 0" class="md:hidden space-y-3">
          <div v-for="wallet in wallets" :key="wallet.id"
               class="bg-gray-50 rounded-lg p-4 space-y-3">
            <div class="flex items-center justify-between">
              <button @click="openWalletDrawer(wallet)"
                      class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                <div class="h-8 w-8 rounded-full flex items-center justify-center shrink-0"
                     :class="{
                       'bg-emerald-100': wallet.type === 'bank',
                       'bg-blue-100': wallet.type === 'card',
                       'bg-purple-100': wallet.type === 'cash'
                     }">
                  <component :is="getWalletTypeIcon(wallet.type)"
                            class="h-4 w-4"
                            :class="{
                              'text-emerald-600': wallet.type === 'bank',
                              'text-blue-600': wallet.type === 'card',
                              'text-purple-600': wallet.type === 'cash'
                            }" />
                </div>
                <div>
                  <span class="font-medium text-gray-900">{{ wallet.name }}</span>
                  <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="{
                          'bg-emerald-100 text-emerald-800': wallet.type === 'bank',
                          'bg-blue-100 text-blue-800': wallet.type === 'card',
                          'bg-purple-100 text-purple-800': wallet.type === 'cash'
                        }">
                    {{ wallet.type }}
                  </span>
                </div>
              </button>
              <div class="flex items-center gap-1">
                <button @click="showBalanceHistory(wallet)"
                        class="p-1 text-gray-400 hover:text-blue-600 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </button>
                <button @click="editWallet(wallet)"
                        class="p-1 text-gray-400 hover:text-blue-600 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button @click="deleteWallet(wallet)"
                        class="p-1 text-gray-400 hover:text-red-600 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-2 text-sm">
              <div>
                <p class="text-gray-500">Balance</p>
                <p class="font-medium text-gray-900">{{ formatCurrency(wallet.balance) }}</p>
              </div>
              <div>
                <p class="text-gray-500">Transactions</p>
                <p class="font-medium text-gray-900">{{ wallet.transaction_count || 0 }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Desktop View -->
        <table v-if="wallets.length > 0" class="hidden md:table min-w-full text-sm">
          <thead>
            <tr class="text-gray-500 border-b">
              <th class="py-2 text-left font-medium">Wallet</th>
              <th class="py-2 text-left font-medium">Type</th>
              <th class="py-2 text-left font-medium">Balance</th>
              <th class="py-2 text-left font-medium">Currency</th>
              <th class="py-2 text-left font-medium">Transactions</th>
              <th class="py-2 text-left font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="wallet in wallets" :key="wallet.id" class="border-b last:border-0">
              <td class="py-1">
                <button @click="openWalletDrawer(wallet)"
                        class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                  <div class="h-6 w-6 rounded-full flex items-center justify-center shrink-0"
                       :class="{
                         'bg-emerald-100': wallet.type === 'bank',
                         'bg-blue-100': wallet.type === 'card',
                         'bg-purple-100': wallet.type === 'cash'
                       }">
                    <component :is="getWalletTypeIcon(wallet.type)"
                              class="h-3 w-3"
                              :class="{
                                'text-emerald-600': wallet.type === 'bank',
                                'text-blue-600': wallet.type === 'card',
                                'text-purple-600': wallet.type === 'cash'
                              }" />
                  </div>
                  <span class="font-medium text-gray-900 text-sm">{{ wallet.name }}</span>
                </button>
              </td>
              <td class="py-1">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                      :class="{
                        'bg-emerald-100 text-emerald-800': wallet.type === 'bank',
                        'bg-blue-100 text-blue-800': wallet.type === 'card',
                        'bg-purple-100 text-purple-800': wallet.type === 'cash'
                      }">
                  {{ wallet.type }}
                </span>
              </td>
              <td class="py-1 text-gray-900">
                {{ formatCurrency(wallet.balance) }}
              </td>
              <td class="py-1">
                <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-800">
                  {{ settingsStore.currencyCode }}
                </span>
              </td>
              <td class="py-1 text-gray-600">
                {{ wallet.transaction_count || 0 }} transactions
              </td>
              <td class="py-1">
                <div class="flex items-center gap-1">
                  <button @click="showBalanceHistory(wallet)"
                          class="p-1 text-gray-400 hover:text-blue-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                  </button>
                  <button @click="editWallet(wallet)"
                          class="p-1 text-gray-400 hover:text-blue-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button @click="deleteWallet(wallet)"
                          class="p-1 text-gray-400 hover:text-red-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center justify-center min-h-[400px]">
          <Wallet class="h-10 w-10 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No wallets</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by creating a new wallet.</p>
          <div class="mt-4">
            <button @click="showAddModal = true"
                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-emerald-500 hover:bg-emerald-600">
              Add Wallet
            </button>
          </div>
        </div>
      </template>
    </div>

    <!-- Add/Edit Wallet Modal -->
    <GlobalModal
      v-model="showAddModal"
      :title="editingWallet ? 'Edit Wallet' : 'Add Wallet'"
    >
      <form @submit.prevent="handleSubmit" class="space-y-5">
        <div>
          <input
            type="text"
            v-model="walletForm.name"
            placeholder="Wallet name"
            class="w-full px-3 py-2 border-0 border-b border-gray-200 focus:ring-0 focus:border-emerald-500 text-sm"
            required
          />
        </div>
        <div>
          <input
            type="number"
            v-model="walletForm.balance"
            placeholder="Initial balance"
            step="0.01"
            class="w-full px-3 py-2 border-0 border-b border-gray-200 focus:ring-0 focus:border-emerald-500 text-sm"
            required
          />
        </div>
        <div class="space-y-3">
          <label class="text-sm font-medium text-gray-700">Wallet Type</label>
          <div class="grid grid-cols-3 gap-3">
            <label class="relative flex cursor-pointer rounded-lg border bg-white p-3 shadow-sm focus:outline-none"
                   :class="{
                     'border-emerald-500 ring-2 ring-emerald-500': walletForm.type === 'bank',
                     'border-gray-300': walletForm.type !== 'bank'
                   }">
              <input type="radio"
                     v-model="walletForm.type"
                     value="bank"
                     class="sr-only"
                     required>
              <div class="flex w-full items-center justify-between">
                <div class="flex items-center">
                  <div class="text-sm">
                    <div class="flex items-center gap-2">
                      <Landmark class="h-4 w-4 text-emerald-600" />
                      <p class="font-medium text-gray-900">Bank</p>
                    </div>
                    <!-- <p class="text-gray-500">Bank Account</p> -->
                  </div>
                </div>
              </div>
            </label>

            <label class="relative flex cursor-pointer rounded-lg border bg-white p-3 shadow-sm focus:outline-none"
                   :class="{
                     'border-blue-500 ring-2 ring-blue-500': walletForm.type === 'card',
                     'border-gray-300': walletForm.type !== 'card'
                   }">
              <input type="radio"
                     v-model="walletForm.type"
                     value="card"
                     class="sr-only"
                     required>
              <div class="flex w-full items-center justify-between">
                <div class="flex items-center">
                  <div class="text-sm">
                    <div class="flex items-center gap-2">
                      <CreditCard class="h-4 w-4 text-blue-600" />
                      <p class="font-medium text-gray-900">Card</p>
                    </div>
                    <!-- <p class="text-gray-500">Credit/Debit</p> -->
                  </div>
                </div>
              </div>
            </label>

            <label class="relative flex cursor-pointer rounded-lg border bg-white p-3 shadow-sm focus:outline-none"
                   :class="{
                     'border-purple-500 ring-2 ring-purple-500': walletForm.type === 'cash',
                     'border-gray-300': walletForm.type !== 'cash'
                   }">
              <input type="radio"
                     v-model="walletForm.type"
                     value="cash"
                     class="sr-only"
                     required>
              <div class="flex w-full items-center justify-between">
                <div class="flex items-center">
                  <div class="text-sm">
                    <div class="flex items-center gap-2">
                      <Banknote class="h-4 w-4 text-purple-600" />
                      <p class="font-medium text-gray-900">Cash</p>
                    </div>
                    <!-- <p class="text-gray-500">Physical Cash</p> -->
                  </div>
                </div>
              </div>
            </label>
          </div>
        </div>
<!--        <div>-->
<!--          <select v-model="walletForm.currency"-->
<!--                  class="w-full px-3 py-2 border-0 border-b border-gray-200 focus:ring-0 focus:border-emerald-500 text-sm"-->
<!--                  required>-->
<!--            <option value="USD">USD</option>-->
<!--            <option value="EUR">EUR</option>-->
<!--            <option value="GBP">GBP</option>-->
<!--            <option value="INR">INR</option>-->
<!--          </select>-->
<!--        </div>-->
        <div class="flex justify-end gap-2 pt-2">
          <button type="button"
                  @click="cancelShowModal()"
                  class="px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">
            Cancel
          </button>
          <button type="submit"
                  class="px-3 py-1.5 text-sm font-medium text-white bg-emerald-500 rounded-md hover:bg-emerald-600"
                  :disabled="isSubmitting">
            {{ isSubmitting ? 'Saving...' : (editingWallet ? 'Save' : 'Add') }}
          </button>
        </div>
      </form>
    </GlobalModal>

    <!-- Transfer Modal -->
    <GlobalModal
      v-model="showTransferModal"
      title="Transfer Between Wallets"
    >
      <WalletTransfer
        :wallets="wallets"
        @close="showTransferModal = false"
        @transfer-complete="handleTransferComplete"
      />
    </GlobalModal>

    <!-- Balance History Modal -->
    <GlobalModal
      v-model="showBalanceHistoryModal"
      :title="selectedWallet ? `${selectedWallet.name} Balance History` : 'Balance History'"
    >
      <WalletBalanceHistory
        v-if="selectedWallet"
        :wallet-id="selectedWallet.id"
      />
    </GlobalModal>

    <!-- Delete Confirmation Modal -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div v-if="showDeleteDialog"
           class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm"
           @click="showDeleteDialog = false">
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="relative bg-white rounded-lg shadow-lg max-w-sm w-full mx-4 p-6"
               @click.stop>
            <h3 class="text-lg font-medium text-gray-900 mb-2">
              Delete Wallet
            </h3>
            <p class="text-sm text-gray-500 mb-6">
              Are you sure you want to delete "{{ walletToDelete?.name }}"? This action cannot be undone.
            </p>
            <div class="flex justify-end gap-3">
              <button type="button"
                      @click="showDeleteDialog = false"
                      class="px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">
                Cancel
              </button>
              <button type="button"
                      @click="confirmDelete"
                      class="px-3 py-1.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Wallet Drawer -->
    <WalletDrawer
      v-model="showWalletDrawer"
      :wallet="selectedWallet"
    />
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, Transition } from 'vue'
import { Wallet, CreditCard, Banknote, Landmark } from 'lucide-vue-next'
import GlobalModal from '../components/Global/GlobalModal.vue'
import WalletTransfer from '../components/WalletTransfer.vue'
import WalletBalanceHistory from '../components/WalletBalanceHistory.vue'
import WalletDrawer from '../components/WalletDrawer.vue'
import { useWalletStore } from '../store/wallet'
import { useNotifications } from '../composables/useNotifications'
import { useSettingsStore } from '../store/settings'
import { useLoaderStore } from '../store/loader'

export default {
  name: 'WalletsView',
  components: {
    GlobalModal,
    Transition,
    Wallet,
    CreditCard,
    Banknote,
    Landmark,
    WalletTransfer,
    WalletBalanceHistory,
    WalletDrawer
  },
  setup() {
    const loading = ref(false)
    const isSubmitting = ref(false)
    const showAddModal = ref(false)
    const showTransferModal = ref(false)
    const showBalanceHistoryModal = ref(false)
    const showDeleteDialog = ref(false)
    const showWalletDrawer = ref(false)
    const walletToDelete = ref(null)
    const editingWallet = ref(null)
    const selectedWallet = ref(null)
    const walletStore = useWalletStore()
    const settingsStore = useSettingsStore()
    const { notify } = useNotifications()

    const walletForm = ref({
      name: '',
      type: 'bank',
      balance: 0,
      currency: 'USD'
    })

    // Computed properties for wallets and pagination
    const wallets = computed(() => walletStore.wallets)
    const pagination = computed(() => walletStore.pagination)
    const totalPages = computed(() => pagination.value?.last_page || 1)
    const startIndex = computed(() => pagination.value?.from || 0)
    const endIndex = computed(() => pagination.value?.to || 0)
    const totalItems = computed(() => pagination.value?.total || 0)
    const paginationLinks = computed(() => pagination.value?.links || [])

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: settingsStore.currencyCode || 'USD'
      }).format(amount)
    }

    const editWallet = (wallet) => {
      editingWallet.value = wallet
      walletForm.value = { ...wallet }
      showAddModal.value = true
    }

    const deleteWallet = (wallet) => {
      walletToDelete.value = wallet
      showDeleteDialog.value = true
    }

    const confirmDelete = async () => {
      if (!walletToDelete.value) return

      try {
        await walletStore.removeWallet(walletToDelete.value.id)
        notify({
          title: 'Success',
          message: 'Wallet deleted successfully',
          type: 'success'
        })
        showDeleteDialog.value = false
        walletToDelete.value = null
      } catch (error) {
        notify({
          title: 'Error',
          message: error.response.data.message,
          type: 'error'
        })
      }finally {
          showDeleteDialog.value = false
          walletToDelete.value = null
      }
    }
      const cancelShowModal = () => {
        showAddModal.value = false
      resetForm()
      }
    const handleSubmit = async () => {
      isSubmitting.value = true
      try {
        if (editingWallet.value) {
          await walletStore.updateWallet(editingWallet.value.id, walletForm.value)
          notify({
            title: 'Success',
            message: 'Wallet updated successfully',
            type: 'success'
          })
        } else {
          await walletStore.addWallet(walletForm.value)
          notify({
            title: 'Success',
            message: 'Wallet created successfully',
            type: 'success'
          })
        }
        showAddModal.value = false
        resetForm()
      } catch (error) {
        notify({
          title: 'Error',
          message: error.message || 'Failed to save wallet',
          type: 'error'
        })
      } finally {
        isSubmitting.value = false
        await walletStore.fetchWallets()
      }
    }

    const resetForm = () => {
      walletForm.value = {
        name: '',
        type: 'bank',
        balance: 0,
        currency: 'USD'
      }
      editingWallet.value = null
    }

    const handlePageChange = async (url) => {
      if (!url) return
      const page = new URL(url).searchParams.get('page')
      if (!page) return

      loading.value = true
      try {
        await walletStore.fetchWallets(page)
      } catch (error) {
        notify({
          title: 'Error',
          message: 'Failed to load wallets',
          type: 'error'
        })
      } finally {
        loading.value = false
      }
    }

    const getWalletTypeIcon = (type) => {
      switch (type) {
        case 'bank':
          return Landmark
        case 'card':
          return CreditCard
        case 'cash':
          return Banknote
        default:
          return Wallet
      }
    }

    const radioClasses = computed(() => ({
      bank: {
        base: 'border-emerald-500 ring-2 ring-emerald-500',
        hover: 'hover:border-emerald-500 hover:ring-emerald-500/20',
        icon: 'text-emerald-600'
      },
      card: {
        base: 'border-blue-500 ring-2 ring-blue-500',
        hover: 'hover:border-blue-500 hover:ring-blue-500/20',
        icon: 'text-blue-600'
      },
      cash: {
        base: 'border-purple-500 ring-2 ring-purple-500',
        hover: 'hover:border-purple-500 hover:ring-purple-500/20',
        icon: 'text-purple-600'
      }
    }))

    const showBalanceHistory = (wallet) => {
      selectedWallet.value = wallet
      showBalanceHistoryModal.value = true
    }

    const handleTransferComplete = async () => {
      await walletStore.fetchWallets()
      notify({
        title: 'Success',
        message: 'Transfer completed successfully',
        type: 'success'
      })
    }

    const openWalletDrawer = (wallet) => {
      selectedWallet.value = wallet
      showWalletDrawer.value = true
    }

    // Handle nav add button click
    const handleNavAddClick = () => {
      showAddModal.value = true
    }

    onMounted(async () => {
      // Listen for global nav add button click
      window.addEventListener('nav-add-clicked', handleNavAddClick)

      const loaderStore = useLoaderStore()
      loaderStore.showLoader()
      loading.value = true
      try {
        await walletStore.fetchWallets()
      } catch (error) {
        notify({
          title: 'Error',
          message: 'Failed to load wallets',
          type: 'error'
        })
      } finally {
        loading.value = false
        loaderStore.hideLoader()
      }
    })

    onUnmounted(() => {
      window.removeEventListener('nav-add-clicked', handleNavAddClick)
    })

    return {
      loading,
      isSubmitting,
      showAddModal,
      showTransferModal,
      showBalanceHistoryModal,
      showDeleteDialog,
      showWalletDrawer,
      walletToDelete,
      editingWallet,
      selectedWallet,
      walletForm,
      wallets,
      pagination,
      totalPages,
      startIndex,
      endIndex,
      totalItems,
      paginationLinks,
      formatCurrency,
      editWallet,
      deleteWallet,
      confirmDelete,
      handleSubmit,
        cancelShowModal,
      handlePageChange,
      getWalletTypeIcon,
      radioClasses,
      showBalanceHistory,
      handleTransferComplete,
      openWalletDrawer,
      settingsStore
    }
  }
}
</script>

<style scoped>
/* Add hover effects for radio buttons */
.relative:hover {
  @apply transition-all duration-200;
}

.relative:hover:not(:has(input:checked)) {
  @apply border-gray-400 shadow-md;
}

/* Ensure the radio buttons are properly spaced in the grid */
.grid-cols-3 {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

/* Add a subtle transition for the ring effect */
.ring-2 {
  @apply transition-all duration-200;
}

/* Add hover effect for wallet name button */
button.hover\:opacity-80:hover {
  @apply opacity-80;
}
</style>
