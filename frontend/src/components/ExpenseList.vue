<template>
  <div class="space-y-4 relative pb-24 m-3">
    <!-- Start New Overview Card -->
    <template v-if="getActiveTab === 'daily'">
      <div class="grid mb-4 bg-white rounded-xl shadow-sm p-4">
        <!-- Daily Bar Chart -->
        <DailyBarChart
          :chartData="dailyBarData"
          :currencyCode="currencyCode"
          :selectedDay="selectedDayBar"
          :meta="dailyBarMeta"
          :isLoadingMore="loading"
          @day-select="handleDaySelect"
          @load-more="handleLoadMore"
          @filter-change="handleFilterChange"
          @clear-filter="handleClearFilter"
        >
          <template #actions>
            <button
              @click="showExportModal = true"
              class="flex items-center gap-1.5 px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
            >
              <Download class="h-4 w-4" />
              <span>Export</span>
            </button>
          </template>
        </DailyBarChart>
      </div>
    </template>
      <template v-else>
          <div class="bg-white rounded-xl shadow-sm p-4">
              <div class="bg-gray-50 rounded-lg p-3 space-y-3">
                  <!-- Cost Summary Grid -->
                  <div class="grid grid-cols-2 gap-3">
                      <!-- Monthly -->
                      <div class="bg-white p-3 rounded-lg">
                          <p class="text-xs text-gray-500">Monthly</p>
                          <p class="text-lg font-semibold text-red-600">
                              {{ formatCurrency(nextMonthPayable, currencyCode) }}
                          </p>
                      </div>
                      <!-- Yearly -->
                      <div class="bg-white p-3 rounded-lg">
                          <p class="text-xs text-gray-500">Yearly</p>
                          <p class="text-lg font-semibold text-orange-600">
                              {{ formatCurrency(totalYearlyCost, currencyCode) }}
                          </p>
                      </div>
                  </div>

                  <!-- EMI Details - Show only if there are EMI type expenses -->
                  <template v-if="hasEMIExpenses">
                      <hr class="border-gray-200">
                      <div class="space-y-2">
                          <h4 class="text-xs font-medium text-gray-700">EMI Summary</h4>
                          <div class="grid grid-cols-2 gap-2 text-sm">
                              <div class="bg-white p-2 rounded-lg">
                                  <p class="text-xs text-gray-500">Interest Paid</p>
                                  <p class="font-medium text-blue-600">
                                      {{ formatCurrency(getTotalInterestPaid, currencyCode) }}
                                  </p>
                              </div>
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

                  <!-- Quick Stats -->
                  <div class="flex justify-between text-sm">
                      <span class="text-gray-600">Total Paid Till Now</span>
                      <span class="font-medium text-green-600">{{ formatCurrency(totalPaidTillNow, currencyCode) }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                      <span class="text-gray-600">Pending This Month</span>
                      <span class="font-medium text-yellow-600">{{ formatCurrency(getTotalPendingPayments, currencyCode) }}</span>
                  </div>

                  <!-- Upcoming Payments Preview -->
                  <template v-if="getUpcomingPayments.length">
                      <hr class="border-gray-200">
                      <div class="space-y-2">
                          <h4 class="text-xs font-medium text-gray-700">Upcoming Payments</h4>
                          <div class="space-y-1">
                              <div v-for="payment in getUpcomingPayments.slice(0, 3)" :key="payment.date"
                                   class="flex justify-between text-sm">
                                  <span class="text-gray-500">{{ payment.name }} - {{ formatDate(payment.date) }}</span>
                                  <span class="font-medium text-gray-700">{{ formatCurrency(payment.amount, currencyCode) }}</span>
                              </div>
                          </div>
                      </div>
                  </template>
              </div>
          </div>

          <!-- Smart Suggestions -->
          <div v-if="suggestions.length > 0" class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-sm p-4">
              <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center gap-2">
                      <Sparkles class="h-4 w-4 text-blue-600" />
                      <h3 class="text-sm font-medium text-gray-800">Detected Patterns</h3>
                  </div>
                  <span class="text-xs text-gray-500">{{ suggestions.length }} found</span>
              </div>
              <div class="space-y-2">
                  <div v-for="suggestion in suggestions.slice(0, 3)" :key="suggestion.name"
                       @click="addFromSuggestion(suggestion)"
                       class="flex items-center justify-between bg-white rounded-lg p-3 cursor-pointer hover:shadow-md transition-all">
                      <div class="flex-1">
                          <h4 class="font-medium text-gray-800">{{ suggestion.name }}</h4>
                          <p class="text-xs text-gray-500">
                              {{ formatCurrency(suggestion.amount, currencyCode) }}/{{ suggestion.recurrence }}
                              <span class="ml-2 text-blue-600">{{ suggestion.confidence }}% confidence</span>
                          </p>
                      </div>
                      <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                          + Add
                      </button>
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

        <!-- Recurring Expenses List with Type Grouping -->
        <div v-else class="space-y-4">
          <!-- Group by Type Tabs -->
          <div class="flex gap-2 overflow-x-auto pb-2">
            <button
              v-for="tab in expenseTypeTabs"
              :key="tab.key"
              @click="selectedExpenseType = tab.key"
              class="px-3 py-1.5 text-sm rounded-full whitespace-nowrap transition-colors"
              :class="selectedExpenseType === tab.key
                ? 'bg-blue-100 text-blue-700'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
            >
              {{ tab.label }} ({{ getExpensesByType(tab.key).length }})
            </button>
          </div>

          <!-- Desktop View -->
          <div class="hidden sm:block space-y-3">
            <div v-for="expense in filteredExpensesByType" :key="expense.id" @click="editRecurringExpense(expense)"
              class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-all cursor-pointer">
              <div class="flex items-center gap-3">
                <!-- Icon with Category Color -->
                <div class="w-10 h-10 rounded-full flex items-center justify-center"
                     :style="{ backgroundColor: getExpenseColor(expense) + '15', color: getExpenseColor(expense) }">
                  <component :is="getExpenseIcon(expense)" class="h-5 w-5" />
                </div>

                <!-- Details -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2">
                    <h4 class="font-medium text-gray-900">{{ expense.name }}</h4>
                    <span class="px-2 py-0.5 text-xs rounded-full"
                          :class="getTypeBadgeClass(expense.type)">
                      {{ capitalizeFirstLetter(expense.type) }}
                    </span>
                  </div>
                  <p class="text-sm text-gray-500">
                    {{ expense.recurrence === 'monthly' ? 'Every' : expense.recurrence === 'quarterly' ? 'Every 3 months,' : 'Yearly,' }}
                    {{ expense.payment_day }}{{ getDayOrdinal(expense.payment_day) }}
                    <span v-if="expense.category" class="ml-1">• {{ expense.category.name }}</span>
                  </p>
                </div>

                <!-- Amount -->
                <div class="text-right">
                  <p class="font-medium text-gray-900">
                    {{ formatCurrency(expense.amount, currencyCode) }}
                  </p>
                  <p class="text-xs text-gray-400">{{ formatCurrency(expense.yearly_cost, currencyCode) }}/yr</p>
                </div>

                <!-- Next Payment -->
                <div class="text-right min-w-[100px]">
                  <p class="text-xs text-gray-500">Next payment</p>
                  <p class="text-sm text-gray-700">
                    {{ expense.next_payment_date ? formatDate(expense.next_payment_date) : 'Completed' }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Mobile View -->
          <div class="sm:hidden space-y-3">
            <div v-for="expense in filteredExpensesByType" :key="expense.id" @click="editRecurringExpense(expense)"
              class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-all cursor-pointer">
              <div class="flex items-center gap-3">
                <!-- Icon with Category Color -->
                <div class="w-10 h-10 rounded-full flex items-center justify-center"
                     :style="{ backgroundColor: getExpenseColor(expense) + '15', color: getExpenseColor(expense) }">
                  <component :is="getExpenseIcon(expense)" class="h-5 w-5" />
                </div>

                <!-- Details -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2">
                    <h4 class="font-medium text-gray-900 truncate">{{ expense.name }}</h4>
                    <span class="px-1.5 py-0.5 text-xs rounded-full flex-shrink-0"
                          :class="getTypeBadgeClass(expense.type)">
                      {{ expense.type === 'subscription' ? 'Sub' : expense.type === 'emi' ? 'EMI' : capitalizeFirstLetter(expense.type) }}
                    </span>
                  </div>
                  <div class="flex items-center gap-2 text-sm text-gray-500">
                    <span>{{ formatCurrency(expense.amount, currencyCode) }}/{{ expense.recurrence === 'monthly' ? 'mo' : expense.recurrence === 'quarterly' ? 'qtr' : 'yr' }}</span>
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
                          {{ transaction.category ? capitalizeFirstLetter(transaction.category.name) : '-' }}
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
                          {{ transaction.category ? capitalizeFirstLetter(transaction.category.name) : '-' }}
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
                      {{ transaction.category ? capitalizeFirstLetter(transaction.category.name) : '-' }}
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
        <RecurringExpenseForm v-else :editingExpense="editingRecurring" :suggestion="selectedSuggestion" :loading="saving" @save="handleRecurringExpenseSave" @delete="handleRecurringExpenseDelete" @cancel="closeModal" />
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
          <RecurringExpenseForm :editingExpense="editingRecurring" :suggestion="selectedSuggestion" :loading="saving" @save="handleRecurringExpenseSave"
            @delete="handleRecurringExpenseDelete" @cancel="closeModal" />
        </template>
      </BottomSheet>
    </div>

    <!-- Export Modal -->
    <ExportModal :show="showExportModal" @close="showExportModal = false" @exported="showExportModal = false" />
  </div>
</template>
<script>
import {
  Calendar, Trash2, Plus, Car, ReceiptIcon, Video, BriefcaseMedical, Gift, Circle, CircleEllipsis, Pizza, CircleDollarSign,
  HandCoins, ChartCandlestick, Landmark,
  Citrus, ShoppingBag, House, Receipt, Clapperboard, Plane, Contact,
  Cross, ShoppingCart, Book, BriefcaseBusiness, BadgeDollarSign,
  Dumbbell, Sparkle, Sparkles, SearchIcon, Tv, Zap, Wifi, Music, Cloud, Smartphone,
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
import { useLoaderStore } from '../store/loader';
import { iconMixin } from '../mixins/iconMixin';
import GlobalModal from './Global/GlobalModal.vue'
import TransactionsDoubleLineBarChart from './Stats/TransactionsDoubleLineBarChart.vue'
import DailyBarChart from './Stats/DailyBarChart.vue'
import ExportModal from './ExportModal.vue'
import { Wallet, CreditCard, Banknote, Download } from 'lucide-vue-next'
export default {
  name: 'ExpenseList',
  mixins: [numberMixin,iconMixin],
  components: {
    Calendar, Trash2, Plus, ShoppingBag, ReceiptIcon, Video, BriefcaseMedical, Gift, Circle, CircleEllipsis, Pizza, CircleDollarSign,
    BottomSheet,
    AddTransaction, HandCoins, ChartCandlestick, Landmark,
    Citrus, House, Receipt, Clapperboard, Plane, Contact,
    Cross, ShoppingCart, Book, BriefcaseBusiness, BadgeDollarSign, Car, SearchIcon,
    Dumbbell, Sparkle, Sparkles, Tv, Zap, Wifi, Music, Cloud, Smartphone,
    CircleDot, CircleX, TrendingUp, TrendingDown, ArrowUpCircle, ArrowDownCircle,
    PiggyBank, RepeatIcon, CalendarClock,
    Dialog, DialogPanel, TransitionRoot, TransitionChild, RecurringExpenseForm, GlobalModal,
    TransactionsDoubleLineBarChart,
    DailyBarChart,
    ExportModal,
    Wallet, CreditCard, Banknote, Download
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
      periodTab: 'W',
      showExportModal: false,
      selectedExpenseType: 'all',
      selectedSuggestion: null,
      expenseTypeTabs: [
        { key: 'all', label: 'All' },
        { key: 'subscription', label: 'Subscriptions' },
        { key: 'bill', label: 'Bills' },
        { key: 'emi', label: 'EMIs' },
        { key: 'other', label: 'Other' }
      ]
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
      'selectedBar',
      'dailyBarData',
      'dailyBarMeta',
      'selectedDayBar',
      'loading'
    ]),
    ...mapState(useSettingsStore, ['currencySymbol', 'currencyCode', 'categories']),
    ...mapState(useWalletStore, ['wallets']),

    // Recurring Expense
    ...mapState(useRecurringExpenseStore, ['recurringExpenses', 'summary', 'suggestions']),

    getActiveTab() {
      return this.activeTab.toLowerCase()
    },
    totalYearlyCost() {
      return this.summary.total_yearly_cost || 0;
    },
    filteredExpensesByType() {
      if (this.selectedExpenseType === 'all') {
        return this.recurringExpenses;
      }
      return this.recurringExpenses.filter(e => e.type === this.selectedExpenseType);
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
        // Fetch suggestions in background
        this.fetchSuggestions()
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
      'fetchBarTransactions',
      'fetchDailyBarData',
      'loadMoreDailyBarData',
      'fetchDailyBarDataByDateRange',
      'setSelectedDayBar',
      'clearDailyBarFilters'
    ]),
    ...mapActions(useSettingsStore, ['fetchSettings', 'fetchCategories']),
    ...mapActions(useWalletStore, ['fetchWallets']),
    // Recurring Expense
    ...mapActions(useRecurringExpenseStore, [
      'fetchRecurringExpenses',
      'addRecurringExpense',
      'updateRecurringExpense',
      'removeRecurringExpense',
      'fetchSuggestions'
    ]),
    getExpensesByType(type) {
      if (type === 'all') return this.recurringExpenses;
      return this.recurringExpenses.filter(e => e.type === type);
    },
    getExpenseColor(expense) {
      if (expense.category?.color) return expense.category.color;
      const typeColors = {
        subscription: '#8B5CF6',
        emi: '#F59E0B',
        bill: '#10B981',
        other: '#6B7280'
      };
      return typeColors[expense.type] || '#6B7280';
    },
    getExpenseIcon(expense) {
      if (expense.icon) return expense.icon;
      const typeIcons = {
        subscription: 'Tv',
        emi: 'Landmark',
        bill: 'Zap',
        other: 'RepeatIcon'
      };
      return typeIcons[expense.type] || 'RepeatIcon';
    },
    getTypeBadgeClass(type) {
      const classes = {
        subscription: 'bg-purple-100 text-purple-700',
        emi: 'bg-amber-100 text-amber-700',
        bill: 'bg-green-100 text-green-700',
        other: 'bg-gray-100 text-gray-700'
      };
      return classes[type] || 'bg-gray-100 text-gray-700';
    },
    addFromSuggestion(suggestion) {
      this.selectedSuggestion = suggestion;
      this.editingRecurring = null;
      if (window.innerWidth >= 768) {
        this.showDesktopModal = true;
      } else {
        this.showAddModal = true;
      }
    },
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
        this.removeTransaction(id)
        this.closeModal()

        // Refresh daily bar chart and transactions
        await this.fetchDailyBarData(60)
        if (this.selectedDayBar) {
          await this.fetchBarTransactions('D', [this.selectedDayBar.date, this.selectedDayBar.date])
        }
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
      this.selectedSuggestion = null
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
        }

        // Refresh daily bar chart and transactions
        await this.fetchWallets()
        await this.fetchDailyBarData(60)

        if (this.selectedDayBar) {
          await this.fetchBarTransactions('D', [this.selectedDayBar.date, this.selectedDayBar.date])
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
    async handleDaySelect(day) {
      this.setSelectedDayBar(day);
      await this.fetchBarTransactions('D', [day.date, day.date]);
    },
    async handleLoadMore() {
      await this.loadMoreDailyBarData();
    },
    async handleFilterChange(filters) {
      await this.fetchDailyBarDataByDateRange(filters.start_date, filters.end_date);
      if (this.selectedDayBar) {
        await this.fetchBarTransactions('D', [this.selectedDayBar.date, this.selectedDayBar.date]);
      }
    },
    async handleClearFilter() {
      this.clearDailyBarFilters();
      await this.fetchDailyBarData();
      if (this.selectedDayBar) {
        await this.fetchBarTransactions('D', [this.selectedDayBar.date, this.selectedDayBar.date]);
      }
    },
  },
  async created() {
    const loaderStore = useLoaderStore();
    loaderStore.showLoader();

    try {
      await this.fetchSettings();
      await this.fetchWallets();
      await this.fetchTransactions(this.dateFilter);
      if (this.getActiveTab === 'recurring') {
        await this.fetchRecurringExpenses();
        this.setRecurringSummary();
      }
      const store = useTransactionStore();
      // Fetch daily bar data for the custom chart
      await store.fetchDailyBarData();
      if (store.selectedDayBar) {
        await store.fetchBarTransactions('D', [store.selectedDayBar.date, store.selectedDayBar.date]);
      }
    } catch (error) {
    } finally {
      loaderStore.hideLoader();
    }
  }
}
</script>
