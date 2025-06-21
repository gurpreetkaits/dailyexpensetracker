<template>
  <div class="space-y-4">
    <!-- Add Transaction Bar -->
    <div class="bg-white rounded-xl shadow-sm px-4 py-3 mb-4 flex items-center justify-between gap-2">
      <div class="flex-shrink-0 flex gap-2">
        <button @click="openAddModal" class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-medium px-5 py-2 rounded-lg shadow transition-all">
          <Plus class="h-4 w-4" />
          Add Transaction
        </button>
        <button 
          @click="handleImportClick"
          class="flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium px-5 py-2 rounded-lg shadow transition-all group relative"
          title="Import your bank statements or any other excel file (Premium Feature)"
        >
          <Upload class="h-4 w-4" />
          Import
          <Sparkle class="h-3 w-3 text-blue-500 ml-1" />
          <span class="absolute -top-2 -right-2 bg-blue-100 text-blue-600 text-[10px] px-1.5 py-0.5 rounded-full">PRO</span>
        </button>
      </div>
      <div class="flex-shrink-0">
        <div v-if="totalPages > 1" class="flex items-center gap-1">
          <button @click="handlePageChange(currentPage - 1)" :disabled="currentPage === 1" class="px-2 py-1 rounded border text-sm disabled:opacity-50" :class="currentPage === 1 ? 'text-gray-400' : 'text-gray-700 hover:bg-gray-50'">Previous</button>
          <button v-for="page in displayedPages" :key="page" @click="handlePageChange(page)" class="px-2 py-1 rounded border text-sm" :class="currentPage === page ? 'bg-emerald-500 text-white border-emerald-500' : 'text-gray-700 hover:bg-gray-50'">{{ page }}</button>
          <button @click="handlePageChange(currentPage + 1)" :disabled="currentPage === totalPages" class="px-2 py-1 rounded border text-sm disabled:opacity-50" :class="currentPage === totalPages ? 'text-gray-400' : 'text-gray-700 hover:bg-gray-50'">Next</button>
        </div>
      </div>
    </div>

    <!-- Transactions Table -->
    <div>
      <div v-if="paginatedTransactions.length > 0" class="bg-white rounded-xl shadow-sm p-4 overflow-x-auto min-h-[440px]">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-gray-500 border-b">
              <th class="py-2 text-left font-medium">Category</th>
              <th class="py-2 text-left font-medium">Type</th>
              <th class="py-2 text-left font-medium">Note</th>
              <th class="py-2 text-left font-medium">Ref Number</th>
              <th class="py-2 text-left font-medium">Amount</th>
              <th class="py-2 text-left font-medium">Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="transaction in paginatedTransactions" :key="transaction.id" class="border-b last:border-0">
              <td class="py-2 flex items-center gap-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center" :style="{
                  backgroundColor: (transaction.category?.color + '15') || (transaction.type === 'income' ? '#e6ffed' : '#ffeded'),
                  color: transaction.category?.color || (transaction.type === 'income' ? '#16a34a' : '#dc2626')
                }">
                  <span class="text-lg">{{ getCategoryEmoji(transaction.category?.icon) }}</span>
                </div>
                <span>{{ transaction.category ? capitalizeFirstLetter(transaction.category.name) : '-' }}</span>
              </td>
              <td class="py-2">
                <span :class="transaction.type === 'expense' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'" class="px-2 py-0.5 rounded-full text-xs capitalize">
                  {{ transaction.type }}
                </span>
              </td>
              <td class="py-2">{{ transaction.note }}</td>
              <td class="py-2">{{ transaction.reference_number || '-' }}</td>
              <td class="py-2">{{ formatCurrency(transaction.amount, currencyCode) }}</td>
              <td class="py-2">{{ formatDate(transaction.transaction_date) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="bg-white rounded-xl shadow-sm p-4 flex flex-col items-center justify-center min-h-[440px]">
        <span class="text-gray-400 text-lg font-medium">No Transactions</span>
      </div>
    </div>

    <!-- Modals -->
    <AddTransaction
      v-if="showAddModal"
      @close="closeAddModal"
      @transaction-added="handleTransactionAdded"
    />

    <ImportTransactionsModal
      v-model="showImportModal"
      @import-started="handleImportStarted"
    />
  </div>
</template>

<script>
import { Plus, Upload, Sparkle } from 'lucide-vue-next'
import AddTransaction from '../AddTransaction.vue'
import ImportTransactionsModal from './ImportTransactionsModal.vue'
import { useTransactionStore } from '../../store/transaction'
import { useSettingsStore } from '../../store/settings'
import { usePolarStore } from '../../store/polar'
import { mapActions, mapState } from 'pinia'
import { numberMixin } from '../../mixins/numberMixin'
import { iconMixin } from '../../mixins/iconMixin'
import { useNotifications } from '../../composables/useNotifications'

export default {
  name: 'TransactionListings',
  components: {
    Plus,
    Upload,
    Sparkle,
    AddTransaction,
    ImportTransactionsModal
  },
  mixins: [numberMixin, iconMixin],
  props: {
    transactions: {
      type: Array,
      required: true
    },
    totalPages: {
      type: Number,
      required: true
    },
    currentPage: {
      type: Number,
      required: true
    },
    displayedPages: {
      type: Array,
      required: true
    },
    currencyCode: {
      type: String,
      required: true
    }
  },
  emits: ['page-change', 'transaction-added'],
  data() {
    return {
      showAddModal: false,
      showImportModal: false
    }
  },
  computed: {
    ...mapState(useTransactionStore, ['loading']),
    ...mapState(usePolarStore, ['hasActiveSubscription']),
    paginatedTransactions() {
      return this.transactions
    }
  },
  methods: {
    ...mapActions(useTransactionStore, ['importTransactions']),
    ...mapActions(usePolarStore, ['initiateCheckout']),
    handlePageChange(page) {
      this.$emit('page-change', page)
    },
    openAddModal() {
      this.showAddModal = true
    },
    closeAddModal() {
      this.showAddModal = false
    },
    handleTransactionAdded(transaction) {
      this.$emit('transaction-added', transaction)
      this.closeAddModal()
    },
    async handleImportClick() {
      console.log('hasActiveSubscription', this.hasActiveSubscription)
      if (!this.hasActiveSubscription) {
        const { notify } = useNotifications()
        notify({
          title: 'Pro Feature',
          message: 'Please upgrade to Pro plan to use import feature',
          type: 'info'
        })
        await this.initiateCheckout('monthly')
        return
      }
      this.showImportModal = true
    },
    async handleImportStarted(transactions) {
      if (!this.hasActiveSubscription) {
        const { notify } = useNotifications()
        notify({
          title: 'Pro Feature',
          message: 'Please upgrade to Pro plan to use import feature',
          type: 'error'
        })
        return
      }

      try {
        try {
          await this.importTransactions(transactions)
          this.$emit('transaction-added')
        } catch (error) {
          console.error('Error importing transactions:', error)
          const { notify } = useNotifications()
          notify({
            title: 'Error',
            message: error.response?.data?.error || 'Failed to import transactions',
            type: 'error'
          })
        }
      } catch (error) {
        console.error('Error importing transactions:', error)
        const { notify } = useNotifications()
        notify({
          title: 'Error',
          message: error.response?.data?.error || 'Failed to import transactions',
          type: 'error'
        })
      }
    }
  }
}
</script>
