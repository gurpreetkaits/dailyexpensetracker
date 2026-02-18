<template>
  <div class="max-w-7xl mx-auto relative">
    <div class="relative pb-24 px-3 pt-2">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <div class="flex justify-between items-center">
          <div class="flex items-center gap-4">
            <h1 class="text-lg font-semibold text-gray-900">Wallet Transfers</h1>
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

      <!-- Transfers List -->
      <div class="bg-white rounded-xl shadow-sm p-4 overflow-x-auto min-h-[440px] relative">
        <!-- Loading State -->
        <div v-if="loading" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-10">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500"></div>
        </div>

        <template v-if="!loading">
          <!-- Mobile View -->
          <div v-if="transfers.length > 0" class="md:hidden space-y-3">
            <div v-for="transfer in transfers" :key="transfer.id"
                 class="bg-gray-50 rounded-lg p-4 space-y-3">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                    <ArrowLeftRight class="h-4 w-4 text-blue-600" />
                  </div>
                  <div>
                    <span class="font-medium text-gray-900">{{ formatCurrency(transfer.amount) }}</span>
                  </div>
                </div>
                <span class="text-xs text-gray-500">{{ formatDate(transfer.transferred_at) }}</span>
              </div>
              <div class="flex items-center gap-2 text-sm">
                <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-800">
                  {{ transfer.from_wallet?.name || 'Unknown' }}
                </span>
                <ArrowRight class="h-3 w-3 text-gray-400" />
                <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-800">
                  {{ transfer.to_wallet?.name || 'Unknown' }}
                </span>
              </div>
              <p v-if="transfer.note" class="text-sm text-gray-500">{{ transfer.note }}</p>
            </div>
          </div>

          <!-- Desktop View -->
          <table v-if="transfers.length > 0" class="hidden md:table min-w-full text-sm">
            <thead>
              <tr class="text-gray-500 border-b">
                <th class="py-2 text-left font-medium">Date</th>
                <th class="py-2 text-left font-medium">From</th>
                <th class="py-2 text-left font-medium"></th>
                <th class="py-2 text-left font-medium">To</th>
                <th class="py-2 text-left font-medium">Amount</th>
                <th class="py-2 text-left font-medium">Note</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="transfer in transfers" :key="transfer.id" class="border-b last:border-0">
                <td class="py-2 text-gray-600">
                  {{ formatDate(transfer.transferred_at) }}
                </td>
                <td class="py-2">
                  <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    {{ transfer.from_wallet?.name || 'Unknown' }}
                  </span>
                </td>
                <td class="py-2">
                  <ArrowRight class="h-4 w-4 text-gray-400" />
                </td>
                <td class="py-2">
                  <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ transfer.to_wallet?.name || 'Unknown' }}
                  </span>
                </td>
                <td class="py-2 font-medium text-gray-900">
                  {{ formatCurrency(transfer.amount) }}
                </td>
                <td class="py-2 text-gray-500">
                  {{ transfer.note || '-' }}
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Empty State -->
          <div v-if="transfers.length === 0" class="flex flex-col items-center justify-center min-h-[400px]">
            <ArrowLeftRight class="h-10 w-10 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No transfers yet</h3>
            <p class="mt-1 text-sm text-gray-500">Transfer money between wallets to see history here.</p>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { ArrowLeftRight, ArrowRight } from 'lucide-vue-next'
import { getWalletTransfers } from '../services/WalletService'
import { useNotifications } from '../composables/useNotifications'
import { useSettingsStore } from '../store/settings'
import { useLoaderStore } from '../store/loader'

export default {
  name: 'TransfersView',
  components: {
    ArrowLeftRight,
    ArrowRight
  },
  setup() {
    const loading = ref(false)
    const transfers = ref([])
    const pagination = ref({})
    const settingsStore = useSettingsStore()
    const { notify } = useNotifications()

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

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const fetchTransfers = async (page = 1) => {
      loading.value = true
      try {
        const response = await getWalletTransfers(page)
        transfers.value = response.data || []
        pagination.value = {
          current_page: response.current_page || 1,
          per_page: response.per_page || 15,
          total: response.total || 0,
          last_page: response.last_page || 1,
          from: response.from || 0,
          to: response.to || 0,
          links: response.links || []
        }
      } catch (error) {
        notify({
          title: 'Error',
          message: 'Failed to load transfers',
          type: 'error'
        })
      } finally {
        loading.value = false
      }
    }

    const handlePageChange = async (url) => {
      if (!url) return
      const page = new URL(url).searchParams.get('page')
      if (!page) return
      await fetchTransfers(page)
    }

    onMounted(async () => {
      const loaderStore = useLoaderStore()
      loaderStore.showLoader()
      await fetchTransfers()
      loaderStore.hideLoader()
    })

    return {
      loading,
      transfers,
      pagination,
      totalPages,
      startIndex,
      endIndex,
      totalItems,
      paginationLinks,
      formatCurrency,
      formatDate,
      handlePageChange,
      settingsStore
    }
  }
}
</script>
