<template>
  <div class="space-y-4 relative pb-24 m-3">
    <!-- Start New Overview Card -->
    <template v-if="getActiveTab === 'daily'">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 bg-white rounded-xl shadow-sm p-4">
        <!-- Activity Card -->
        <div class="h-[300px] ">
          <TransactionsDoubleLineBarChart
            :chartData="activityBarDataV2"
            :currencyCode="currencyCode"
            :period="periodTab"
            :selectedBar="selectedBar"
            @period-change="handlePeriodChange"
            @bar-click="handleBarClick"
          />
        </div>
        <!-- Recent Wallet Card -->
        <div
          v-if="recentWallet"
          class="relative overflow-hidden rounded-xl p-4 flex flex-col justify-between lg:h-[300px] sm:h-[200px]"
          :class="{
            'bg-gradient-to-br from-emerald-500 to-emerald-600': recentWallet.type === 'bank',
            'bg-gradient-to-br from-blue-500 to-blue-600': recentWallet.type === 'card',
            'bg-gradient-to-br from-purple-500 to-purple-600': recentWallet.type === 'cash'
          }"
        >
          <!-- Decorative Elements -->
          <div class="absolute inset-0 opacity-10 pointer-events-none select-none">
            <div class="absolute -right-4 -top-4 h-20 w-20 md:h-24 md:w-24 rounded-full bg-white"></div>
            <div class="absolute -left-4 -bottom-4 h-24 w-24 md:h-32 md:w-32 rounded-full bg-white"></div>
          </div>
          <div class="relative z-10">
            <div class="flex items-center gap-3 mb-2">
              <div :class="walletIconBgClass(recentWallet.type)" class="w-10 h-10 rounded-full flex items-center justify-center bg-white/20">
                <component :is="walletIconComponent(recentWallet.type)" class="h-5 w-5 text-white" />
              </div>
              <div>
                <div class="font-medium text-white">{{ recentWallet.name }}</div>
                <div class="text-xs text-white/80 capitalize">{{ recentWallet.type }} Wallet</div>
              </div>
            </div>
            <div class="flex justify-between items-center mb-2">
              <div>
                <div class="text-xs text-white/80">Balance</div>
                <div class="font-semibold text-white">{{ formatCurrency(recentWallet.balance, currencyCode) }}</div>
              </div>
              <div>
                <div class="text-xs text-white/80">Currency</div>
                <div class="font-semibold text-white">{{ recentWallet.currency }}</div>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="bg-white rounded-xl shadow-sm p-4 flex flex-col justify-between">
          <div class="text-gray-400 text-sm">No recent wallet activity</div>
        </div>
      </div>
    </template>
      <template v-else>
          <div class="bg-white rounded-xl shadow-sm p-4">
              <div>
                  <div class="bg-gray-50 rounded-lg p-3 space-y-3">
                      <!-- Total Monthly Payable -->
                      <div class="flex justify-between mb-2">
                          <span class="text-xs font-medium text-gray-700">Total Monthly Payable</span>
                          <p class="text-sm font-medium text-red-700">
                              {{ formatCurrency(nextMonthPayable, currencyCode) }}
                          </p>
                      </div>

                      <!-- EMI Details - Show only if there are EMI type expenses -->
                      <template v-if="hasEMIExpenses">
                          <hr class="border-gray-200">
                          <div class="space-y-2">
                              <h4 class="text-xs font-medium text-gray-700">EMI Summary</h4>

                              <div class="grid grid-cols-2 gap-2 text-sm">
                                  <!-- Interest Paid -->
                                  <div class="bg-white p-2 rounded-lg">
                                      <p class="text-xs text-gray-500">Interest Paid</p>
                                      <p class="font-medium text-blue-600">
                                          {{ formatCurrency(getTotalInterestPaid, currencyCode) }}
                                      </p>
                                  </div>

                                  <!-- Remaining Balance -->
                                  <div class="bg-white p-2 rounded-lg">
                                      <p class="text-xs text-gray-500">Remaining Balance</p>
                                      <p class="font-medium text-blue-600">
                                          {{ formatCurrency(getTotalRemainingBalance, currencyCode) }}
                                      </p>
                                  </div>
                              </div>
                          </div>
                      </template>

                      <hr class="border-gray-200">

                      <!-- Total Paid -->
                      <div class="flex justify-between">
                          <span class="text-xs font-medium text-gray-700">Total Paid Till Now</span>
                          <p class="text-sm font-medium text-green-700">
                              {{ formatCurrency(totalPaidTillNow, currencyCode) }}
                          </p>
                      </div>

                      <!-- Pending Payments -->
                      <div class="flex justify-between">
                          <span class="text-xs font-medium text-gray-700">Pending Payments</span>
                          <p class="text-sm font-medium text-yellow-600">
                              {{ formatCurrency(getTotalPendingPayments, currencyCode) }}
                          </p>
                      </div>

                      <!-- Upcoming Payments Preview -->
                      <template v-if="getUpcomingPayments.length">
                          <hr class="border-gray-200">
                          <div class="space-y-2">
                              <h4 class="text-xs font-medium text-gray-700">Upcoming Payments</h4>
                              <div class="space-y-1">
                                  <div v-for="payment in getUpcomingPayments.slice(0, 3)" :key="payment.date"
                                       class="flex justify-between text-sm">
                                      <span class="text-gray-500">{{ formatDate(payment.date) }}</span>
                                      <span class="font-medium text-gray-700">
                  {{ formatCurrency(payment.amount, currencyCode) }}
                </span>
                                  </div>
                              </div>
                          </div>
                      </template>
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
            <div class="hidden sm:block">
              <!-- Empty State for Desktop -->
              <div v-if="!barTransactions || barTransactions.length === 0 && !saving"
                class="flex flex-col items-center justify-center py-12 px-4 bg-white rounded-xl shadow-sm">
                <Receipt class="h-16 w-16 text-gray-300 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                  No Transactions
                </h3>
                <p class="text-gray-500 text-center text-sm mb-6 max-w-sm">
                  Start tracking your expenses and income to get insights into your financial habits.
                </p>
              </div>

              <!-- Desktop Grid View -->
              <div v-else class="grid grid-cols-2 gap-4">
                <!-- Expenses Column -->
                <div class="space-y-3">
                  <h3 class="text-base font-medium text-gray-900 px-1 flex items-center gap-2">
                    <ArrowDownCircle class="h-4 w-4 text-red-500" />
                    Expenses
                  </h3>
                  <div v-for="transaction in barTransactions.filter(t => t.type === 'expense')" :key="transaction.id"
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
                        <p class="text-sm text-gray-500 truncate" :title="transaction.note">{{ transaction.note }}</p>
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
                  <div v-for="transaction in barTransactions.filter(t => t.type === 'income')" :key="transaction.id"
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
                        <p class="text-sm text-gray-500 truncate" :title="transaction.note">{{ transaction.note }}</p>
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
            </div>
            <!-- Mobile View -->
            <div class="sm:hidden space-y-3">
              <!-- Empty State -->
              <div v-if="!barTransactions || barTransactions.length === 0 && !saving"
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
              <div v-else v-for="transaction in barTransactions" :key="transaction.id"
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
                    <p class="text-xs text-gray-500 truncate max-w-[100px]" :title="transaction.note">{{ transaction.note }}</p>
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
      class="fixed bottom-[80px] left-1/2 transform -translate-x-1/2 inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-500 text-white shadow-xl hover:bg-emerald-600 hover:shadow-2xl transition-all">
      <Plus class="h-6 w-6" />
    </button>
    <!-- Start Desktop Model -->
    <GlobalModal v-model="showDesktopModal" :title="getActiveTab === 'daily' ? (editingTransaction ? 'Edit Transaction' : 'New Transaction') : 'Recurring Expense'" size="max-w-md">
      <template #default>
        <AddTransaction v-if="getActiveTab === 'daily'" @transaction-added="handleTransactionAdded" @remove="removeItem" :item="editingTransaction" @close="closeModal" />
        <RecurringExpenseForm v-else :editingExpense="editingRecurring" :loading="saving" @save="handleRecurringExpenseSave" @delete="handleRecurringExpenseDelete" @cancel="closeModal" />
      </template>
    </GlobalModal>
    <!-- End Desktop Model -->
    <!-- Bottom Sheet -->
    <div class="md:hidden">
      <BottomSheet v-model="showAddModal">
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
  </div>
</template>
<script>
import {
  Calendar, Trash2, Plus, Car, ReceiptIcon, Video, BriefcaseMedical, Gift, Circle, CircleEllipsis, Pizza, CircleDollarSign
  , HandCoins, ChartCandlestick, Landmark,
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
import { useWalletStore } from '../store/wallet';
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { useRecurringExpenseStore } from '../store/recurringExpense';
import { iconMixin } from '../mixins/iconMixin';
import GlobalModal from './Global/GlobalModal.vue'
import TransactionsDoubleLineBarChart from './Stats/TransactionsDoubleLineBarChart.vue'
import { Wallet, CreditCard, Banknote } from 'lucide-vue-next'
export default {
  name: 'ExpenseList',
  mixins: [numberMixin,iconMixin],
  components: {
    Calendar, Trash2, Plus, ShoppingBag, ReceiptIcon, Video, BriefcaseMedical, Gift, Circle, CircleEllipsis, Pizza, CircleDollarSign,
    BottomSheet,
    AddTransaction, HandCoins, ChartCandlestick, Landmark,
    Citrus, House, Receipt, Clapperboard, Plane, Contact,
    Cross, ShoppingCart, Book, BriefcaseBusiness, BadgeDollarSign, Car,SearchIcon,
    Dumbbell,
    Sparkle,
    CircleDot, CircleX, TrendingUp,
    TrendingDown,
    ArrowUpCircle,
    ArrowDownCircle,
    PiggyBank, RepeatIcon, CalendarClock,
    Dialog, DialogPanel, TransitionRoot, TransitionChild, RecurringExpenseForm, GlobalModal,
    TransactionsDoubleLineBarChart,
    Wallet, CreditCard, Banknote
  },
  setup() {
    const walletStore = useWalletStore();
    return { walletStore };
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
      searchTimeout: null,
      periodTab: 'D',
    }
  },
  computed: {
    ...mapState(useTransactionStore, [
      'transactions',
      'getBalance',
      'getSavingRate',
      'getFilteredIncome',
      'getFilteredExpenses',
      'barTransactions',
      'selectedBar'
    ]),
    ...mapState(useSettingsStore, ['currencySymbol', 'currencyCode', 'categories']),
    ...mapState(useWalletStore, ['wallets']),

    // Recurring Expense
    ...mapState(useRecurringExpenseStore, ['recurringExpenses','summary']),

    getActiveTab() {
      return this.activeTab.toLowerCase()
    },
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

      if (this.dateFilter === 'Today') {
        filtered = filtered.filter(t => new Date(t.transaction_date) >= today);
      } else if (this.dateFilter === 'Weekly') {
        filtered = filtered.filter(t => new Date(t.transaction_date) >= thisWeek);
      } else if (this.dateFilter === 'Monthly') {
        filtered = filtered.filter(t => new Date(t.transaction_date) >= thisMonth);
      } else if (this.dateFilter === 'Yearly') {
        filtered = filtered.filter(t => new Date(t.transaction_date) >= thisYear);
      }

      return filtered;
    },
    totalIncome() {
      return this.dateFilteredTransactions
        .filter((trans) => trans.type == 'income')
        .map((value) => Number(value.amount))
        .reduce((sum, amount) => sum + amount, 0)
    },

    // Recurring Expenses
    nextMonthPayable() {
      return this.summary.monthly_payment_total || 0
    },

    totalPaidTillNow() {
      return this.summary.total_amount_paid || 0
    },

    getTotalInterestPaid() {
      return this.summary.total_interest_paid || 0
    },

    getTotalRemainingBalance() {
      return this.summary.total_remaining_balance || 0
    },

    getTotalPendingPayments() {
      return this.summary.total_pending_payments || 0
    },

    getUpcomingPayments() {
      return this.summary.upcoming_payments || []
    },

    hasEMIExpenses() {
      return this.recurringExpenses.some(expense => expense.type === 'emi')
    },
    recentTransaction() {
      return this.transactions && this.transactions.length > 0 ? this.transactions[0] : null
    },
    recentWallet() {
      if (!this.recentTransaction) return null;
      const walletId = this.recentTransaction.wallet_id;
      return this.wallets?.find(w => w.id === walletId);
    },
    activityBarDataV2() {
      const store = useTransactionStore();
      return store.activityBarDataV2 || [];
    },
    selectedPeriod() {
      return this.periodTab
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
    ...mapActions(useTransactionStore, [
      'addTransaction',
      'fetchTransactions',
      'updateTransaction',
      'removeTransaction',
      'searchTransactions',
      'fetchActivityBarDataV2',
      'fetchBarTransactions'
    ]),
    ...mapActions(useSettingsStore, ['fetchSettings', 'fetchCategories']),
    ...mapActions(useWalletStore, ['fetchWallets']),
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
        } else {
          await this.addRecurringExpense(expense)
        }
        this.closeModal()
        this.setRecurringSummary()
      } catch (error) {
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
        this.closeModal()
        this.setRecurringSummary()
      } catch (error) {
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
        await this.fetchBarTransactions(this.periodTab, [this.selectedBar.start, this.selectedBar.end])
        this.removeTransaction(id)
        this.closeModal()
      } catch (e) {
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
          // For updates, ensure we have the correct ID
          const transactionData = {
            ...params,
            id: this.editingTransaction.id
          };

          await this.updateTransaction(transactionData)

          if (this.getActiveTab === 'daily') {
            await this.fetchBarTransactions(this.periodTab)
          }
        } else {
          // For new transactions
          await this.fetchWallets()

          // Get current week's start and end dates in ISO format
          const now = new Date()
          const startOfWeek = new Date(now.setDate(now.getDate() - now.getDay()))
          startOfWeek.setHours(0, 0, 0, 0)
          const endOfWeek = new Date(startOfWeek)
          endOfWeek.setDate(startOfWeek.getDate() + 6)
          endOfWeek.setHours(23, 59, 59, 999)

          await this.fetchBarTransactions(this.periodTab, [
            startOfWeek.toISOString().split('T')[0],
            endOfWeek.toISOString().split('T')[0]
          ])
        }
        this.editingTransaction = null
      } catch (e) {
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
      async setRecurringSummary() {
          try {
              await this.fetchRecurringExpenses();

              // Set the monthly payable from the current expense
              if (this.details?.expense) {
                  const expense = this.recurringExpenses.find(e => e.id === this.details.expense.id);
                  this.nextMonthPayable = expense?.is_active ? parseFloat(expense.amount) : 0;
              }

              // Set total paid from details
              this.totalPaidTillNow = this.details?.total_paid || 0;

          } catch (error) {
          }
      },
    walletIconComponent(type) {
      switch (type) {
        case 'bank': return Landmark
        case 'card': return CreditCard
        case 'cash': return Banknote
        default: return Wallet
      }
    },
    walletIconBgClass(type) {
      switch (type) {
        case 'bank': return 'bg-emerald-100 text-emerald-600'
        case 'card': return 'bg-blue-100 text-blue-600'
        case 'cash': return 'bg-purple-100 text-purple-600'
        default: return 'bg-gray-100 text-gray-600'
      }
    },
    async handlePeriodChange(period) {
      this.periodTab = period;
      try {
        await this.fetchActivityBarDataV2(period);
        if (this.selectedBar) {
          await this.fetchBarTransactions(period, [this.selectedBar.start, this.selectedBar.end]);
        }
      } catch (error) {
      }
    },
    async handleBarClick(bar) {
      await this.fetchBarTransactions(this.periodTab, [bar.start, bar.end])
    },
  },
  async created() {
    try {
      await this.fetchSettings();
      await this.fetchWallets();
      await this.fetchTransactions(this.dateFilter);
      if (this.getActiveTab === 'recurring') {
        await this.fetchRecurringExpenses();
        this.setRecurringSummary();
      }
      const store = useTransactionStore();
      await store.fetchActivityBarDataV2(this.periodTab);
      if (store.selectedBar) {
        await store.fetchBarTransactions(this.periodTab, [store.selectedBar.start, store.selectedBar.end]);
      }
    } catch (error) {
    }
  }
}
</script>
