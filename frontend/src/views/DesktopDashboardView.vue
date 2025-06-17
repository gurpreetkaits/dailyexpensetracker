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

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm px-4 py-2 mb-4">
      <div class="flex flex-wrap items-center gap-2">
        <!-- Search Filter -->
        <div class="flex-grow min-w-[180px] max-w-xs">
          <div class="relative">
            <input 
              type="text" 
              v-model="filters.search" 
              @input="debouncedSearch"
              placeholder="Search transactions..." 
              class="w-full px-3 py-1.5 text-sm bg-gray-50/50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 focus:bg-white transition-all duration-200 hover:border-gray-300 cursor-pointer"
            />
            <SearchIcon class="absolute right-2.5 top-2 h-4 w-4 text-gray-400" />
          </div>
        </div>
        
        <!-- Type Filter -->
        <div class="min-w-[110px]">
          <select 
            v-model="filters.type" 
            @change="applyFilters"
            class="w-full px-3 py-1.5 text-sm bg-gray-50/50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 focus:bg-white transition-all duration-200 hover:border-gray-300 cursor-pointer"
          >
            <option value="all">All Types</option>
            <option value="expense">Expense</option>
            <option value="income">Income</option>
          </select>
        </div>
        
        <!-- Category Filter -->
        <div class="min-w-[130px]">
          <select 
            v-model="filters.category_id" 
            @change="applyFilters"
            class="w-full px-3 py-1.5 text-sm bg-gray-50/50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 focus:bg-white transition-all duration-200 hover:border-gray-300 cursor-pointer"
          >
            <option :value="null">All Categories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ capitalizeFirstLetter(category.name) }}
            </option>
          </select>
        </div>
        
        <!-- Wallet Filter -->
        <div class="min-w-[120px]">
          <select 
            v-model="filters.wallet_id" 
            @change="applyFilters"
            class="w-full px-3 py-1.5 text-sm bg-gray-50/50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 focus:bg-white transition-all duration-200 hover:border-gray-300 cursor-pointer"
          >
            <option :value="null">All Wallets</option>
            <option v-for="wallet in wallets" :key="wallet.id" :value="wallet.id">
              {{ wallet.name.toUpperCase() }}
            </option>
          </select>
        </div>
        
        <!-- Date Filter -->
        <div class="min-w-[120px]">
          <select 
            v-model="filters.filter" 
            @change="applyFilters"
            class="w-full px-3 py-1.5 text-sm bg-gray-50/50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 focus:bg-white transition-all duration-200 hover:border-gray-300 cursor-pointer"
          >
            <option value="all">All Time</option>
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="weekly">This Week</option>
            <option value="monthly">This Month</option>
            <option value="yearly">This Year</option>
          </select>
        </div>
        
        <!-- Date Range Picker -->
        <div class="flex items-center gap-1 min-w-[220px]">
          <input 
            type="date" 
            v-model="filters.date_from" 
            @change="applyFilters"
            class="flex-1 px-3 py-1.5 text-sm bg-gray-50/50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 focus:bg-white transition-all duration-200 hover:border-gray-300 cursor-pointer"
          />
          <span class="text-gray-500 text-sm">-</span>
          <input 
            type="date" 
            v-model="filters.date_to" 
            @change="applyFilters"
            class="flex-1 px-3 py-1.5 text-sm bg-gray-50/50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 focus:bg-white transition-all duration-200 hover:border-gray-300 cursor-pointer"
          />
        </div>
        
        <!-- Reset Filters Button -->
        <div>
          <button 
            @click="resetFilters" 
            class="px-3 py-1.5 text-sm border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
          >
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Filter/Add/Pagination Card Bar -->
    <div class="bg-white rounded-xl shadow-sm px-4 py-3 mb-4">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <!-- Add Transaction Button -->
        <div class="flex-shrink-0 mb-2 sm:mb-0">
          <button @click="openAddModal" class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-medium px-5 py-2 rounded-lg shadow transition-all">
            <Plus class="h-4 w-4" />
            Add Transaction
          </button>
        </div>

        <!-- Pagination Controls -->
        <div class="flex-shrink-0 w-full sm:w-auto">
          <div v-if="paginationLinks.length > 3" class="flex flex-wrap items-center justify-end gap-1">
            <button 
              v-for="link in paginationLinks" 
              :key="link.label"
              @click="handlePageChange(link.url)"
              :disabled="!link.url"
              class="px-2 py-1 rounded border text-sm disabled:opacity-50"
              :class="[
                link.active 
                  ? 'bg-emerald-500 text-white border-emerald-500' 
                  : 'text-gray-700 hover:bg-gray-50',
                !link.url ? 'text-gray-400' : ''
              ]"
              v-html="link.label"
            >
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Transactions Table with Pagination -->
    <div>
      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-xl shadow-sm p-4 flex items-center justify-center min-h-[440px]">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500"></div>
      </div>

      <div v-else-if="transactions.length > 0" class="bg-white rounded-xl shadow-sm p-4 overflow-x-auto min-h-[440px]">
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
            <tr v-for="transaction in transactions" :key="transaction.id" class="border-b last:border-0">
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

        <!-- Pagination Info -->
        <div class="mt-4 flex justify-between items-center text-sm text-gray-500">
          <div>
            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} transactions
          </div>
        </div>
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
import { useCategoryStore } from '../store/category'
import { useWalletStore } from '../store/wallet'
import { iconMixin } from '../mixins/iconMixin'
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
      filters: {
        search: '',
        type: 'all',
        category_id: null,
        wallet_id: null,
        filter: 'all',
        date_from: null,
        date_to: null
      },
      searchTimeout: null,
      currentPage: 1,
      saving: false,
    }
  },
  computed: {
    transactions() {
      return useTransactionStore().transactions
    },
    pagination() {
      return useTransactionStore().pagination
    },
    paginationLinks() {
      return this.pagination.links || []
    },
    currencyCode() {
      return useSettingsStore().currencyCode
    },
    loading() {
      return useTransactionStore().loading
    },
    categories() {
      return useCategoryStore().categories
    },
    wallets() {
      return useWalletStore().wallets
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
      return this.transactions.length
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
  },
  created() {
    this.loadInitialData();
  },
  mounted() {
    this.fetchTransactions();
  },
  methods: {
    async loadInitialData() {
      await useSettingsStore().fetchSettings();
      
      // Only fetch categories and wallets if they're not already loaded
      if (!useCategoryStore().categories.length) {
        await useCategoryStore().fetchCategories();
      }
      
      if (!useWalletStore().wallets.length) {
        await useWalletStore().fetchWallets();
      }
    },
    async fetchTransactions() {
      await useTransactionStore().fetchPaginatedTransactions(this.currentPage, this.filters);
    },
    async fetchData() {
      // Only fetch transactions, not categories and wallets
      await this.fetchTransactions();
    },
    async handleTransactionAdded(transaction) {
      this.showAddModal = false;
      await useTransactionStore().addTransaction(transaction);
      await this.fetchTransactions();
    },
    openAddModal() {
      this.editingTransaction = null;
      this.showAddModal = true;
    },
    closeModal() {
      this.showAddModal = false;
      this.editingTransaction = null;
    },
    handlePageChange(url) {
      if (!url) return;
      
      // Extract page number from URL
      const pageMatch = url.match(/page=(\d+)/);
      if (pageMatch && pageMatch[1]) {
        this.currentPage = parseInt(pageMatch[1]);
        this.fetchTransactions();
      }
    },
    applyFilters() {
      this.currentPage = 1;
      this.fetchTransactions();
    },
    debouncedSearch() {
      if (this.searchTimeout) {
        clearTimeout(this.searchTimeout);
      }
      
      this.searchTimeout = setTimeout(() => {
        this.applyFilters();
      }, 500);
    },
    resetFilters() {
      this.filters = {
        search: '',
        type: 'all',
        category_id: null,
        wallet_id: null,
        filter: 'all',
        date_from: null,
        date_to: null
      };
      this.currentPage = 1;
      this.fetchTransactions();
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
  }
}
</script>
