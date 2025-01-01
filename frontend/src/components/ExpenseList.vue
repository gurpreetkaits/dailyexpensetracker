<template>
  <div class="space-y-4 relative pb-24 m-3">
    <!-- Start New Overview Card -->
    <template v-if="getActiveTab === 'daily'">
      <div class="bg-white rounded-xl shadow-sm p-4"><!-- Search Toggle Button / Input -->

        <div>
          <div class="flex items-center justify-end mb-3">
            <h2 class="text-sm font-medium text-gray-700"></h2>
              <div class="relative mr-2">
                  <template v-if="showSearch">
                      <div class="flex items-center bg-gray-50 border border-gray-100 rounded-lg">
                          <input
                              v-model="searchQuery"
                              type="text"
                              @keydown="handleSearch"
                              placeholder="Search by note..."
                              class="text-xs px-2 py-1 bg-transparent outline-none w-40 rounded-md"
                          />
                          <button
                              @click="showSearch = false; searchQuery = ''"
                              class="p-1 text-gray-400 hover:text-gray-600"
                          >
                              <CircleX class="h-4 w-4" />
                          </button>
                      </div>
                  </template>
                  <button
                      v-else
                      @click="showSearch = true"
                      class="p-1 text-gray-500 hover:text-gray-700"
                  >
                      <SearchIcon class="h-4 w-4" />
                  </button>
              </div>
            <select v-model="dateFilter"
              class="text-xs bg-gray-50 border border-gray-100 rounded-lg px-2 py-1 text-gray-600">
              <option value="Today">Today</option>
              <option value="Weekly">This Week</option>
              <option value="Yesterday">Yesterday</option>
              <option value="Monthly">This Month</option>
              <option value="Yearly">This Year</option>
            </select>
          </div>
          <div class="bg-gray-50 rounded-lg p-3">
            <div class="flex justify-between mb-2">
              <span class="text-xs font-medium text-gray-700">{{ dateFilter }} Earnings</span>
              <p class="text-sm font-medium text-green-700">
                {{ formatCurrency(getFilteredIncome, currencyCode) }}
              </p>
            </div>
            <div class="flex justify-between">
              <span class="text-xs font-medium text-gray-700">{{ dateFilter }} Expenses</span>
              <p class="text-sm font-medium text-red-700">
                {{ formatCurrency(getFilteredExpenses, currencyCode) }}
              </p>
            </div>
            <hr class="my-2">
            <div class="flex justify-between">
              <span class="text-xs font-medium text-gray-700">Balance</span>
              <p class="text-sm font-medium text-blue-700">
                {{ formatCurrency(balance, currencyCode) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </template>
    <template v-else>
      <div class="bg-white rounded-xl shadow-sm p-4">
        <div>
          <div class="bg-gray-50 rounded-lg p-3">
            <div class="flex justify-between mb-2">
              <span class="text-xs font-medium text-gray-700">Total Monthly Payable</span>
              <p class="text-sm font-medium text-red-700">
                {{ formatCurrency(nextMonthPayable, currencyCode) }}
              </p>
            </div>
            <div class="flex justify-between">
              <span class="text-xs font-medium text-gray-700">Total Paid Till Now</span>
              <p class="text-sm font-medium text-green-700">
                {{ formatCurrency(totalPaidTillNow, currencyCode) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </template>
    <!-- End New Overview Card -->

    <!-- Switch -->
    <div class="bg-white rounded-2xl shadow-sm p-1 mb-6">
      <div class="grid grid-cols-2 gap-2">
        <button v-for="type in ['Daily', 'Recurring']" :key="type" @click="activeTab = type.toLowerCase()"
          class="py-1 px-2 rounded-xl text-sm font-medium transition-all" :class="getActiveTab === type.toLowerCase()
            ? 'bg-blue-50 text-blue-600'
            : 'text-gray-500 hover:bg-gray-50'">
          {{ type }}
        </button>
      </div>
    </div>
    <!-- End Switch -->
    <div>
      <template v-if="saving">
        <div class="flex justify-center py-4">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        </div>
      </template>

      <!-- Recurring View -->
      <template v-else-if="getActiveTab === 'recurring'">
        <!-- Empty State -->
        <div v-if="!recurringExpenses.length"
          class="flex flex-col items-center justify-center py-12 px-4 bg-white rounded-xl shadow-sm">
          <CalendarClock class="h-16 w-16 text-gray-300 mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            No Recurring Expenses
          </h3>
          <p class="text-gray-500 text-center text-sm mb-6 max-w-sm">
            Start adding your recurring expenses like monthly subscriptions or bills.
          </p>
        </div>

        <!-- Recurring Expenses List -->
        <div v-else class="space-y-3">
          <div class="hidden sm:block space-y-3">
            <div v-for="expense in recurringExpenses" :key="expense.id" @click="editRecurringExpense(expense)"
              class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-all cursor-pointer">
              <div class="flex items-center gap-3">
                <!-- Icon -->
                <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center">
                  <RepeatIcon class="h-5 w-5 text-blue-500" />
                </div>

                <!-- Details -->
                <div class="flex-1 min-w-0">
                  <h4 class="font-medium text-gray-900">{{ expense.name }}</h4>
                  <p class="text-sm text-gray-500">
                    Every {{ expense.payment_day }}{{ getDayOrdinal(expense.payment_day) }}
                  </p>
                </div>

                <!-- Amount -->
                <div class="text-right">
                  <p class="font-medium text-gray-900">
                    {{ formatCurrency(expense.amount, currencyCode) }}
                  </p>
                  <p class="text-sm text-gray-400">per month</p>
                </div>

                <!-- Next Payment -->
                <div class="text-right min-w-[100px]">
                  <p class="text-xs text-gray-500">Next payment</p>
                  <p class="text-sm text-gray-700">
                    {{ formatDate(getNextPaymentDate(expense)) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Mobile View -->
          <div class="sm:hidden space-y-3">
            <div v-for="expense in recurringExpenses" :key="expense.id" @click="editRecurringExpense(expense)"
              class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-all cursor-pointer">
              <div class="flex items-center gap-3">
                <!-- Icon -->
                <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center">
                  <RepeatIcon class="h-5 w-5 text-blue-500" />
                </div>

                <!-- Details -->
                <div class="flex-1 min-w-0">
                  <h4 class="font-medium text-gray-900">{{ expense.name }}</h4>
                  <div class="flex items-center gap-2 text-sm text-gray-500">
                    <span>{{ formatCurrency(expense.amount, currencyCode) }}/mo</span>
                    <span class="text-gray-300">•</span>
                    <span>{{ expense.payment_day }}{{ getDayOrdinal(expense.payment_day) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- Daily Transactions View -->
      <template v-else>
        <div>
          <template v-if="saving">
            <div class="flex justify-center py-4">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            </div>
          </template>
          <template v-else>
            <!-- Desktop View -->
            <div class="hidden sm:grid sm:grid-cols-2 sm:gap-4">
              <!-- Expenses Column -->
              <div class="space-y-3">
                <h3 class="text-base font-medium text-gray-900 px-1 flex items-center gap-2">
                  <ArrowDownCircle class="h-4 w-4 text-red-500" />
                  Expenses
                </h3>
                <div v-for="transaction in filteredTransactions.filter(t => t.type === 'expense')" :key="transaction.id"
                  class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-all cursor-pointer"
                  @click="editTransaction(transaction)">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" :style="{
                      backgroundColor: (transaction.category?.color + '15') || '#fee2e2',
                      color: transaction.category?.color || '#dc2626'
                    }">
                      <component :is="transaction.category?.icon || 'ShoppingBag'" class="h-5 w-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="font-medium text-gray-900 truncate">
                        {{ transaction.category ? capitalizeFirstLetter(transaction.category.name) : transaction.note }}
                      </h4>
                      <p class="text-sm text-gray-500 truncate">{{ transaction.note }}</p>
                    </div>
                    <div class="text-right">
                      <p class="font-medium text-red-600">
                        -{{ formatCurrency(transaction.amount, currencyCode) }}
                      </p>
                      <p class="text-sm text-gray-400">{{ formatDate(transaction.transaction_date) }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Income Column -->
              <div class="space-y-3">
                <h3 class="text-base font-medium text-gray-900 px-1 flex items-center gap-2">
                  <ArrowUpCircle class="h-4 w-4 text-green-500" />
                  Income
                </h3>
                <div v-for="transaction in filteredTransactions.filter(t => t.type === 'income')" :key="transaction.id"
                  class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-all cursor-pointer"
                  @click="editTransaction(transaction)">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" :style="{
                      backgroundColor: (transaction.category?.color + '15') || '#dcfce7',
                      color: transaction.category?.color || '#16a34a'
                    }">
                      <component :is="transaction.category?.icon || 'CircleDollarSign'" class="h-5 w-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="font-medium text-gray-900 truncate">
                        {{ transaction.category ? capitalizeFirstLetter(transaction.category.name) : transaction.note }}
                      </h4>
                      <p class="text-sm text-gray-500 truncate">{{ transaction.note }}</p>
                    </div>
                    <div class="text-right">
                      <p class="font-medium text-green-600">
                        +{{ formatCurrency(transaction.amount, currencyCode) }}
                      </p>
                      <p class="text-sm text-gray-400">{{ formatDate(transaction.transaction_date) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Mobile View -->
            <div class="sm:hidden space-y-3">
              <!-- Empty State -->
              <div v-if="!transactions || transactions.length === 0 && !saving"
                class="flex flex-col items-center justify-center py-12 px-4 bg-white rounded-xl shadow-sm">
                <Receipt class="h-16 w-16 text-gray-300 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                  No Transactions Yet
                </h3>
                <p class="text-gray-500 text-center text-sm mb-6 max-w-sm">
                  Start tracking your expenses and income to get insights into your financial habits.
                </p>
              </div>

              <!-- Transaction List -->
              <div v-else v-for="transaction in filteredTransactions" :key="transaction.id"
                class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-all"
                @click="editTransaction(transaction)">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full flex items-center justify-center" :style="{
                    backgroundColor: (transaction.category?.color + '15') ||
                      (transaction.type === 'income' ? '#dcfce7' : '#fee2e2'),
                    color: transaction.category?.color ||
                      (transaction.type === 'income' ? '#16a34a' : '#dc2626')
                  }">
                    <component
                      :is="transaction.category?.icon || (transaction.type === 'income' ? 'CircleDollarSign' : 'ShoppingBag')"
                      class="h-5 w-5" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="font-medium text-gray-900 truncate">
                      {{ transaction.category ? capitalizeFirstLetter(transaction.category.name) : transaction.note }}
                    </h4>
                    <p class="text-sm text-gray-500 truncate">{{ transaction.note }}</p>
                  </div>
                  <div class="text-right">
                    <p class="font-medium" :class="transaction.type === 'expense' ? 'text-red-600' : 'text-green-600'">
                      {{ transaction.type === 'expense' ? '-' : '+' }}{{ formatCurrency(transaction.amount,
                        currencyCode) }}
                    </p>
                    <p class="text-sm text-gray-400">{{ formatDate(transaction.transaction_date) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </template>
    </div>
    <!-- Recurring Expenseses -->

    <!-- Floating Action Button -->
    <button @click="createNewTransaction"
      class="fixed bottom-[80px] left-1/2 transform -translate-x-1/2 inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-500 text-white shadow-xl hover:bg-blue-600 hover:shadow-2xl transition-all">
      <Plus class="h-6 w-6" />
    </button>
    <!-- Start Desktop Model -->
    <TransitionRoot appear :show="showDesktopModal" as="template">
      <Dialog as="div" @close="closeModal" class="relative z-50 hidden md:block">
        <TransitionChild enter="duration-200 ease-out" enter-from="opacity-0" enter-to="opacity-100"
          leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild enter="duration-300 ease-out" enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95">
              <DialogPanel
                class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all">
                <AddTransaction v-if="getActiveTab === 'daily'" @transaction-added="handleTransactionAdded"
                  @remove="removeItem" :item="editingTransaction" @close="closeModal" />
                <RecurringExpenseForm v-else :editingExpense="editingRecurring" :loading="saving"
                  @save="handleRecurringExpenseSave" @delete="handleRecurringExpenseDelete" @cancel="closeModal" />
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
    <!-- End Desktop Model -->
    <!-- Bottom Sheet -->
    <BottomSheet v-model="showAddModal" class="md:hidden">
      <template v-if="getActiveTab === 'daily'">
        <AddTransaction @transaction-added="handleTransactionAdded" @close="closeModal" @remove="removeItem"
          :item="editingTransaction" />
      </template>
      <template v-else>
        <RecurringExpenseForm :editingExpense="editingRecurring" :loading="saving" @save="handleRecurringExpenseSave"
          @delete="handleRecurringExpenseDelete" @cancel="closeModal" />
      </template>
    </BottomSheet>
  </div>
</template>
<script>
import {
  Calendar, Trash2, Plus, Car, ReceiptIcon, Video, BriefcaseMedical, Gift, Circle, CircleEllipsis, Pizza, CircleDollarSign
  , HandCoins, Wallet, ChartCandlestick, Landmark,
  Citrus, ShoppingBag, House, Receipt, Clapperboard, Plane, Contact,
  Cross, ShoppingCart, Book, BriefcaseBusiness, BadgeDollarSign,
  Dumbbell,
  Sparkle,SearchIcon,
  CircleDot, CircleX, TrendingUp, TrendingDown, ArrowUpCircle, ArrowDownCircle, PiggyBank, CalendarClock, RepeatIcon
} from 'lucide-vue-next'
import BottomSheet from './BottomSheet.vue'
import AddTransaction from './AddTransaction.vue'
import RecurringExpenseForm from './RecurringExpenseForm.vue'
import { useTransactionStore } from '../store/transaction';
import { mapActions, mapState } from 'pinia';
import { numberMixin } from '../mixins/numberMixin';
import { deleteTransaction, getTransactionById } from '../services/TransactionService';
import { useSettingsStore } from '../store/settings';
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { useRecurringExpenseStore } from '../store/recurringExpense';
export default {
  name: 'ExpenseList',
  mixins: [numberMixin],
  components: {
    Calendar, Trash2, Plus, ShoppingBag, ReceiptIcon, Video, BriefcaseMedical, Gift, Circle, CircleEllipsis, Pizza, CircleDollarSign,
    BottomSheet,
    AddTransaction, HandCoins, Wallet, ChartCandlestick, Landmark,
    Citrus, House, Receipt, Clapperboard, Plane, Contact,
    Cross, ShoppingCart, Book, BriefcaseBusiness, BadgeDollarSign, Car,SearchIcon,
    Dumbbell,
    Sparkle,
    CircleDot, CircleX, TrendingUp,
    TrendingDown,
    ArrowUpCircle,
    ArrowDownCircle,
    PiggyBank, RepeatIcon, CalendarClock,
    Dialog, DialogPanel, TransitionRoot, TransitionChild, RecurringExpenseForm
  },
  data() {
    return {
      showAddModal: false,
      showDesktopModal: false,
      searchQuery: '',
      showSearch: false,
      saving: false,
      activeFilter: '',
      dateFilter: 'Today',
      selectedItem: {},
      editingTransaction: null,
      activeTab: 'daily',
      editingRecurring: null,
      editingGoal: false,
      nextMonthPayable: 0,
      totalPaidTillNow: 0,
        searchTimeout: null,
    }
  },
  computed: {
    ...mapState(useTransactionStore, ['transactions', 'getBalance', 'getSavingRate', 'getFilteredIncome', 'getFilteredExpenses','searchTransactions']),
    ...mapState(useSettingsStore, ['currencySymbol', 'currencyCode', 'categories']),

    // Recurring Expense
    ...mapState(useRecurringExpenseStore, ['recurringExpenses']),
    getActiveTab() {
      return this.activeTab.toLowerCase()
    },
    // End Recurring Expense
    filteredTransactions() {
      return this.transactions
    },
    balance() {
      return this.getBalance
    },
    dateFilteredTransactions() {
      const now = new Date();
      const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      const thisWeek = new Date(today.getTime() - (today.getDay() * 24 * 60 * 60 * 1000));
      const thisMonth = new Date(now.getFullYear(), now.getMonth(), 1);
      const thisYear = new Date(now.getFullYear(), 0, 1);

      let filtered = [...this.transactions];

      // Match exact cases from your dropdown
      if (this.dateFilter === 'Today') {
        filtered = filtered.filter(t => new Date(t.transaction_date) >= today);
      } else if (this.dateFilter === 'Weekly') {
        filtered = filtered.filter(t => new Date(t.transaction_date) >= thisWeek);
      } else if (this.dateFilter === 'Monthly') {
        filtered = filtered.filter(t => new Date(t.transaction_date) >= thisMonth);
      } else if (this.dateFilter === 'Yearly') {
        filtered = filtered.filter(t => new Date(t.transaction_date) >= thisYear);
      }
      // If 'All Time' is selected, return all transactions (no filter)

      return filtered;
    },
    totalIncome() {
      return this.dateFilteredTransactions
        .filter((trans) => trans.type == 'income')
        .map((value) => Number(value.amount))
        .reduce((sum, amount) => sum + amount, 0)
    },
  },
  watch: {
    currencySymbol: {
      handler(newVal) {
      },
      immediate: true
    },
    currencyCode: {
      handler(newVal) {
      },
      immediate: true
    },
    dateFilter: {
      async handler(newValue) {
        try {
          this.saving = true;
          await this.fetchTransactions(newValue);
        } catch (error) {
          console.error('Error updating transactions:', error);
        } finally {
          this.saving = false;
        }
      },
      immediate: true
    },
    async getActiveTab(newTab) {
      if (newTab === 'recurring') {
        await this.fetchRecurringExpenses()
        this.setRecurringSummary()
      }
    }
  },
  methods: {
    ...mapActions(useTransactionStore, ['addTransaction', 'fetchTransactions', 'updateTransaction', 'removeTransaction','searchTransactions' ]),
    ...mapActions(useSettingsStore, ['fetchSettings', 'fetchCategories']),
    // Recurring Expense
    ...mapActions(useRecurringExpenseStore, [
      'fetchRecurringExpenses',
      'addRecurringExpense',
      'updateRecurringExpense',
      'removeRecurringExpense'
    ]),
    getDayOrdinal(day) {
      if (day > 3 && day < 21) return 'th'
      switch (day % 10) {
        case 1: return 'st'
        case 2: return 'nd'
        case 3: return 'rd'
        default: return 'th'
      }
    },
    async handleRecurringExpenseSave(expense) {
      try {
        this.saving = true
        if (this.editingRecurring) {
          expense = { ...expense, id: this.editingRecurring.id }
          await this.updateRecurringExpense(expense)
          console.log('Recurring expense updated')
        } else {
          await this.addRecurringExpense(expense)
          console.log('Recurring expense created')
        }
        this.closeModal()
        this.setRecurringSummary()
      } catch (error) {
        console.log('Failed to save recurring expense')
        console.error(error)
      } finally {
        this.saving = false
      }
    },
    async handleRecurringExpenseDelete() {
      try {
        if (!confirm('Are you sure you want to delete this recurring expense?')) {
          return
        }
        if (!this.editingRecurring.id) {
          alert('Record does not exists')
        }
        this.saving = true
        await this.removeRecurringExpense(this.editingRecurring.id)
        console.log('Recurring expense deleted')
        this.closeModal()
        this.setRecurringSummary()
      } catch (error) {
        console.log('Failed to delete recurring expense')
        console.error(error)
      } finally {
        this.saving = false
      }
    },

    editRecurringExpense(expense) {
      this.editingRecurring = expense
      if (window.innerWidth >= 768) {
        this.showDesktopModal = true
      } else {
        this.showAddModal = true
      }
    },
    getNextPaymentDate(expense) {
      const today = new Date()
      const paymentDay = expense.payment_day
      let nextPayment = new Date(today.getFullYear(), today.getMonth(), paymentDay)

      // If we've passed this month's payment date, get next month's
      if (today.getDate() > paymentDay) {
        nextPayment.setMonth(nextPayment.getMonth() + 1)
      }

      return nextPayment
    },
    // End Recurring Expense
    async removeItem(id) {
      try {
        if (!confirm('Sure You want to delete?')) {
          return
        }
        await deleteTransaction(id)
        this.removeTransaction(id)
        this.closeModal()
      } catch (e) {
        console.log(e)
      }
    },
    createNewTransaction() {
      this.editingTransaction = null
      if (window.innerWidth >= 768) {
        this.showDesktopModal = true
      } else {
        this.showAddModal = true
      }
    },
    async editTransaction(transaction) {
      this.editingTransaction = await getTransactionById(transaction.id)
      if (window.innerWidth >= 768) {
        this.showDesktopModal = true
      } else {
        this.showAddModal = true
      }
    },
    closeModal() {
      this.showAddModal = false
      this.showDesktopModal = false
      this.editingRecurring = null
    },
    async handleTransactionAdded(params) {
      this.closeModal()
      try {
        this.saving = true
        if (this.editingTransaction) {
          await this.updateTransaction({ ...params, id: this.editingTransaction.id })
        } else {
          await this.addTransaction(params)
          await this.fetchTransactions(this.dateFilter)
        }
        this.editingTransaction = null
      } catch (e) {
        console.log(e)
      } finally {
        this.saving = false
      }
    },
    formatAmount(amount) {
      if (!amount) return '0.00'
      return amount.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('en-IN', {
        month: 'short',
        day: 'numeric'
      })
    },
    setRecurringSummary() {
      this.nextMonthPayable =0
      this.totalPaidTillNow= 0
      this.recurringExpenses.forEach((value) => {
        const firstPaymentDate = new Date(value.first_payment_date);
        const currentDate = new Date();
        const nextPaymentMonth = new Date(firstPaymentDate);
        nextPaymentMonth.setMonth(nextPaymentMonth.getMonth()); // Payment starts the month after first_payment_date
        // Calculate how many months have passed since the next scheduled payment
        const monthsPassed = this.getMonthsBetween(nextPaymentMonth, currentDate);
        // Calculate payments made till now based on the number of months that have passed
        const paymentsMadeTillNow = monthsPassed > 0 ? monthsPassed * parseFloat(value.amount) : 0;
        // Add the current month's payable if the expense is active and the first payment is in the past
        if (nextPaymentMonth <= currentDate && value.is_active) {
          this.nextMonthPayable += parseFloat(value.amount); // Monthly recurring, same amount each month
        }
        // Add payments made till now to the total
        this.totalPaidTillNow += paymentsMadeTillNow;

      });
    },

    // Helper function to calculate months between two dates
    getMonthsBetween(startDate, endDate) {
      const yearsDiff = endDate.getFullYear() - startDate.getFullYear();
      const monthsDiff = endDate.getMonth() - startDate.getMonth();
      return yearsDiff * 12 + monthsDiff;
    },
      async handleSearch() {
          // Clear any existing timeout
          if (this.searchTimeout) {
              clearTimeout(this.searchTimeout);
          }

          // Set a new timeout to debounce the search
          this.searchTimeout = setTimeout(async () => {
              try {
                  this.saving = true;

                  if (this.searchQuery.trim()) {
                      await this.searchTransactions(this.searchQuery, this.dateFilter);
                  } else {
                      // If search is empty, fetch normal transactions
                      await this.fetchTransactions(this.dateFilter);
                  }
              } catch (error) {
                  console.error('Search failed:', error);
              } finally {
                  this.saving = false;
              }
          }, 300); // 300ms debounce
      },

      clearSearch() {
          this.searchQuery = '';
          this.showSearch = false;
          this.fetchTransactions(this.dateFilter);
      },
  },
  async created() {
    try {
      await this.fetchSettings()
      await this.fetchTransactions(this.dateFilter)
      if (this.getActiveTab === 'recurring') {
        await this.fetchRecurringExpenses()
        this.setRecurringSummary()
      }

    } catch (error) {
      console.error('Error loading transactions:', error)
    }
  }
}
</script>
