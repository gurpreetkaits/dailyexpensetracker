<template>
  <div class="max-w-7xl mx-auto relative">
    <!-- Add Transaction Button -->
    <button @click="openAddModal" class="fixed bottom-8 right-8 z-50 bg-emerald-500 hover:bg-emerald-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-xl transition-all">
      <Plus class="h-7 w-7" />
    </button>

    <!-- GlobalModal implementation -->
    <GlobalModal 
      :model-value="showAddModal" 
      @update:model-value="showAddModal = $event" 
      title="Add Transaction"
      size="max-w-md"
    >
      <AddTransaction 
        @transaction-added="handleTransactionAdded" 
        @close="closeModal" 
        :item="editingTransaction" 
      />
    </GlobalModal>

    <!-- Filter/Add/Pagination Card Bar -->
    <div class="bg-white rounded-xl shadow-sm px-4 py-3 mb-4 flex items-center justify-between gap-2">
      <!-- Add Transaction Button -->
      <div class="flex-shrink-0">
        <button @click="openAddModal" class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-medium px-5 py-2 rounded-lg shadow transition-all">
          <Plus class="h-4 w-4" />
          Add Transaction
        </button>
      </div>

      <!-- Pagination Controls -->
      <div class="flex-shrink-0">
        <div v-if="totalPages > 1" class="flex items-center gap-1">
          <button @click="handlePageChange(currentPage - 1)"
                  :disabled="currentPage === 1"
                  class="px-2 py-1 rounded border text-sm disabled:opacity-50"
                  :class="currentPage === 1 ? 'text-gray-400' : 'text-gray-700 hover:bg-gray-50'">
            Previous
          </button>
          <button v-for="page in displayedPages"
                  :key="page"
                  @click="handlePageChange(page)"
                  class="px-2 py-1 rounded border text-sm"
                  :class="currentPage === page ? 'bg-emerald-500 text-white border-emerald-500' : 'text-gray-700 hover:bg-gray-50'">
            {{ page }}
          </button>
          <button @click="handlePageChange(currentPage + 1)"
                  :disabled="currentPage === totalPages"
                  class="px-2 py-1 rounded border text-sm disabled:opacity-50"
                  :class="currentPage === totalPages ? 'text-gray-400' : 'text-gray-700 hover:bg-gray-50'">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Transactions Table with Pagination -->
    <div>
      <div v-if="paginatedTransactions.length > 0" class="bg-white rounded-xl shadow-sm p-4 overflow-x-auto min-h-[440px]">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-gray-500 border-b">
              <th class="py-2 text-left font-medium">Category</th>
              <th class="py-2 text-left font-medium">Type</th>
              <th class="py-2 text-left font-medium">Note</th>
              <th class="py-2 text-left font-medium">Wallet</th>
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
                  <component :is="transaction.category?.icon || (transaction.type === 'income' ? 'CircleDollarSign' : 'ShoppingBag')" class="h-4 w-4" />
                </div>
                <span>{{ transaction.category ? capitalizeFirstLetter(transaction.category.name) : '-' }}</span>
              </td>
              <td class="py-2">
                <span :class="transaction.type === 'expense' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'" class="px-2 py-0.5 rounded-full text-xs capitalize">
                  {{ transaction.type }}
                </span>
              </td>
              <td class="py-2">{{ transaction.note }}</td>
              <td class="py-2">{{ transaction.wallet?.name.toUpperCase() || '-' }}</td>
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
  </div>
</template>

<script>
import {
  Calendar, Trash2, Plus, Car, ReceiptIcon, Video, BriefcaseMedical, Gift, Circle, CircleEllipsis, Pizza, CircleDollarSign,
  HandCoins, Wallet, ChartCandlestick, Landmark, Citrus, ShoppingBag, House, Receipt, Clapperboard, Plane, Contact,
  Cross, ShoppingCart, Book, BriefcaseBusiness, BadgeDollarSign, Dumbbell, Sparkle, SearchIcon, CircleDot, CircleX,
  TrendingUp, TrendingDown, ArrowUpCircle, ArrowDownCircle, PiggyBank, CalendarClock, RepeatIcon, ChevronLeft, ChevronRight
} from 'lucide-vue-next'
import AddTransaction from '../components/AddTransaction.vue'
import GlobalModal from '../components/Global/GlobalModal.vue'
import { useTransactionStore } from '../store/transaction'
import { useSettingsStore } from '../store/settings'
import { iconMixin } from '../mixins/iconMixin'
import { useCategoryStore } from '../store/category'  
export default {
  name: 'DesktopDashboardView',
  components: {
    AddTransaction,
    GlobalModal,
    Calendar, Trash2, Plus, Car, ReceiptIcon, Video, BriefcaseMedical, Gift, Circle, CircleEllipsis, Pizza, CircleDollarSign,
    HandCoins, Wallet, ChartCandlestick, Landmark, Citrus, ShoppingBag, House, Receipt, Clapperboard, Plane, Contact,
    Cross, ShoppingCart, Book, BriefcaseBusiness, BadgeDollarSign, Dumbbell, Sparkle, SearchIcon, CircleDot, CircleX,
    TrendingUp, TrendingDown, ArrowUpCircle, ArrowDownCircle, PiggyBank, CalendarClock, RepeatIcon, ChevronLeft, ChevronRight, 
  },
  mixins: [iconMixin],
  data() {
    return {
      showAddModal: false,
      editingTransaction: null,
      searchQuery: '',
      currentPage: 1,
      itemsPerPage: 10,
      saving: false
    }
  },
  computed: {
    transactions() {
      return useTransactionStore().transactions
    },
    currencyCode() {
      return useSettingsStore().currencyCode
    },
    categories() {
      return useCategoryStore().categories
    },
    filteredTransactions() {
      return this.transactions
    },
    totalIncome() {
      return this.transactions.filter(t => t.type === 'income').reduce((sum, t) => sum + Number(t.amount), 0)
    },
    totalExpense() {
      return this.transactions.filter(t => t.type === 'expense').reduce((sum, t) => sum + Number(t.amount), 0)
    },
    balance() {
      return this.totalIncome - this.totalExpense
    },
    monthlySpendings() {
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
      const now = new Date()
      const grouped = {}
      this.transactions.forEach(t => {
        if (t.type === 'expense') {
          const date = new Date(t.transaction_date)
          if (date.getFullYear() !== now.getFullYear()) return
          const label = months[date.getMonth()]
          if (!grouped[label]) grouped[label] = 0
          grouped[label] += Number(t.amount)
        }
      })
      const max = Math.max(...Object.values(grouped), 1)
      return Object.entries(grouped).map(([label, amount]) => ({
        label,
        amount,
        percent: Math.round((amount / max) * 100)
      }))
    },
    totalItems() {
      return this.filteredTransactions.length
    },
    totalPages() {
      return Math.ceil(this.totalItems / this.itemsPerPage)
    },
    startIndex() {
      return (this.currentPage - 1) * this.itemsPerPage + 1
    },
    endIndex() {
      return Math.min(this.currentPage * this.itemsPerPage, this.totalItems)
    },
    paginatedTransactions() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      const end = start + this.itemsPerPage
      return this.filteredTransactions.slice(start, end)
    },
    displayedPages() {
      const pages = []
      const maxVisiblePages = 5
      let start = Math.max(1, this.currentPage - Math.floor(maxVisiblePages / 2))
      let end = Math.min(this.totalPages, start + maxVisiblePages - 1)
      if (end - start + 1 < maxVisiblePages) {
        start = Math.max(1, end - maxVisiblePages + 1)
      }
      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      return pages
    }
  },
  methods: {
    async fetchData() {
      await useSettingsStore().fetchSettings()
      await useTransactionStore().fetchTransactions('all')
      await useCategoryStore().fetchCategories()
    },
    async handleTransactionAdded(transaction) {
      this.showAddModal = false
      await useTransactionStore().addTransaction(transaction)
      await this.fetchData()
    },
    openAddModal() {
      this.editingTransaction = null
      this.showAddModal = true
    },
    closeModal() {
      this.showAddModal = false
      this.editingTransaction = null
    },
    handlePageChange(page) {
      if (page < 1 || page > this.totalPages) return
      this.currentPage = page
    },
    formatCurrency(amount, currencyCode) {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currencyCode || 'USD',
      }).format(amount)
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('en-IN', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      })
    },
    capitalizeFirstLetter(str) {
      if (!str) return ''
      return str.charAt(0).toUpperCase() + str.slice(1)
    }
  },
  async created() {
    try {
      await this.fetchData()
    } catch (error) {
      console.error('Error loading transactions:', error)
    }
  }
}
</script>
