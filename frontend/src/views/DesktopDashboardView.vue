<template>
  <div class="max-w-7xl mx-auto relative">
    <!-- Add Transaction Button -->
    <button @click="openAddModal" class="fixed bottom-8 right-8 z-50 bg-emerald-500 hover:bg-emerald-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-xl transition-all">
      <Plus class="h-7 w-7" />
    </button>

    <!-- Add Transaction Modal -->
    <TransitionRoot appear :show="showAddModal" as="template">
      <Dialog as="div" @close="closeModal" class="relative z-50">
        <TransitionChild enter="duration-200 ease-out" enter-from="opacity-0" enter-to="opacity-100"
          leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" />
        </TransitionChild>
        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild enter="duration-300 ease-out" enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95">
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all">
                <AddTransaction @transaction-added="handleTransactionAdded" @close="closeModal" :item="editingTransaction" />
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Top Section: Merged Summary Card and Spendings Chart -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <!-- Compact Merged Summary Card -->
      <div class="bg-white rounded-xl shadow-sm flex flex-col justify-center p-0 overflow-hidden">
        <div class="flex flex-col divide-y divide-gray-100">
          <div class="flex items-center gap-3 px-6 py-4">
            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-green-50">
              <Wallet class="h-4 w-4 text-green-500" />
            </div>
            <span class="text-base font-medium text-gray-500 flex-1">Total Income</span>
            <span class="text-lg font-bold text-green-600">{{ formatCurrency(totalIncome, currencyCode) }}</span>
          </div>
          <div class="flex items-center gap-3 px-6 py-4">
            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-red-50">
              <ShoppingBag class="h-4 w-4 text-red-500" />
            </div>
            <span class="text-base font-medium text-gray-500 flex-1">Total Expense</span>
            <span class="text-lg font-bold text-red-600">{{ formatCurrency(totalExpense, currencyCode) }}</span>
          </div>
          <div class="flex items-center gap-3 px-6 py-4">
            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-blue-50">
              <CircleDollarSign class="h-4 w-4 text-blue-500" />
            </div>
            <span class="text-base font-medium text-gray-500 flex-1">Balance</span>
            <span class="text-lg font-bold text-blue-600">{{ formatCurrency(balance, currencyCode) }}</span>
          </div>
        </div>
      </div>
      <!-- Spendings Chart -->
      <div class="bg-white rounded-lg shadow-sm p-4 h-full flex flex-col">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-base font-semibold">Spendings by Month ({{ new Date().getFullYear() }})</h3>
        </div>
        <div v-if="monthlySpendings.length" class="flex-1">
          <div v-for="month in monthlySpendings" :key="month.label" class="mb-3">
            <div class="flex justify-between mb-1">
              <span class="text-xs text-gray-700">{{ month.label }}</span>
              <span class="text-xs font-medium text-gray-900">{{ formatCurrency(month.amount, currencyCode) }}</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
              <div class="bg-blue-500 h-2 rounded-full" :style="{ width: month.percent + '%' }"></div>
            </div>
          </div>
        </div>
        <div v-else class="text-gray-400 text-sm">No spendings data</div>
      </div>
    </div>

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
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { useTransactionStore } from '../store/transaction'
import { useSettingsStore } from '../store/settings'

export default {
  name: 'DesktopDashboardView',
  components: {
    AddTransaction,
    Dialog, DialogPanel, TransitionRoot, TransitionChild,
    Calendar, Trash2, Plus, Car, ReceiptIcon, Video, BriefcaseMedical, Gift, Circle, CircleEllipsis, Pizza, CircleDollarSign,
    HandCoins, Wallet, ChartCandlestick, Landmark, Citrus, ShoppingBag, House, Receipt, Clapperboard, Plane, Contact,
    Cross, ShoppingCart, Book, BriefcaseBusiness, BadgeDollarSign, Dumbbell, Sparkle, SearchIcon, CircleDot, CircleX,
    TrendingUp, TrendingDown, ArrowUpCircle, ArrowDownCircle, PiggyBank, CalendarClock, RepeatIcon, ChevronLeft, ChevronRight
  },
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
      await useTransactionStore().fetchTransactions()
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
