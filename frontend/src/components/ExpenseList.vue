<template>
  <div class="max-w-7xl mx-auto relative flex flex-col overflow-y-hidden" style="height: calc(100vh - 80px)">
    <!-- Fixed Header: Switch + Wallets -->
    <div class="flex-shrink-0 bg-gray-100 z-10">
      <!-- Top Tabs -->
      <div class="flex justify-center px-3 pt-3 pb-2">
        <div class="switch-container">
          <div class="switch-indicator" :class="getActiveTab === 'recurring' ? 'switch-right' : ''"></div>
          <button
            v-for="type in ['Daily', 'Recurring']"
            :key="type"
            @click="setActiveTab(type.toLowerCase())"
            class="switch-btn"
            :class="getActiveTab === type.toLowerCase() ? 'active' : ''"
          >
            {{ type }}
          </button>
        </div>
      </div>

      <!-- Wallets Row -->
      <div
        v-if="getActiveTab === 'daily' && wallets.length > 0"
        class="relative group"
        @mouseenter="showWalletArrows = true"
        @mouseleave="showWalletArrows = false"
      >
        <!-- Left Arrow -->
        <button
          v-show="showWalletArrows && canScrollWalletsLeft"
          @click="scrollWallets('left')"
          class="absolute left-0 top-1/2 -translate-y-1/2 z-10 w-7 h-7 rounded-full bg-white/90 hover:bg-gray-100 border border-gray-200 shadow-sm flex items-center justify-center transition-all opacity-0 group-hover:opacity-100"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <!-- Right Arrow -->
        <button
          v-show="showWalletArrows && canScrollWalletsRight"
          @click="scrollWallets('right')"
          class="absolute right-0 top-1/2 -translate-y-1/2 z-10 w-7 h-7 rounded-full bg-white/90 hover:bg-gray-100 border border-gray-200 shadow-sm flex items-center justify-center transition-all opacity-0 group-hover:opacity-100"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>

        <div
          ref="walletsContainer"
          class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide px-3"
          @scroll="updateWalletScrollState"
        >
      <router-link
        v-for="wallet in wallets"
        :key="wallet.id"
        to="/wallets"
        class="wallet-card flex-shrink-0 bg-white rounded-xl shadow-sm p-2.5 hover:shadow-md transition-shadow"
      >
        <div class="flex items-center gap-1.5 mb-1.5">
          <div class="w-6 h-6 rounded-lg flex items-center justify-center flex-shrink-0"
               :class="walletIconBgClass(wallet.type)">
            <component :is="walletIconComponent(wallet.type)" class="w-3 h-3" />
          </div>
          <p class="text-[10px] text-gray-500 truncate flex-1">{{ wallet.name }}</p>
        </div>
        <p class="text-xs font-semibold text-gray-900 mb-1.5">{{ formatCurrency(wallet.balance || 0, currencyCode) }}</p>
        <!-- Sparkline -->
        <svg class="w-full h-6" viewBox="0 0 100 24" preserveAspectRatio="none">
          <path
            :d="getSparklinePath(wallet.sparkline || [wallet.balance || 0])"
            fill="none"
            :stroke="getSparklineColor(wallet.sparkline)"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      </router-link>
        </div>
      </div>
    </div>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto px-3 pt-3 pb-24 space-y-4 scrollbar-hide">
    <!-- Start New Overview Card -->
    <template v-if="getActiveTab === 'daily'">
      <div class="grid mb-4 bg-white rounded-xl shadow-sm p-4 tab-content">
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
          <!-- Summary Cards -->
          <div class="space-y-3 tab-content">
              <!-- Total Balance Card (Dark) -->
              <div class="bg-[#0F1115] text-white rounded-xl p-4 relative overflow-hidden shadow-sm">
                  <div class="flex items-start justify-between gap-3">
                      <div class="flex-1 min-w-0">
                          <p class="text-gray-400 text-[10px] font-medium mb-1">Total Outstanding</p>
                          <p class="text-xl font-semibold truncate">{{ formatCurrency(summary.total_remaining_balance || 0, currencyCode) }}</p>
                      </div>
                      <div v-if="summary.emi_count > 0" class="flex items-center gap-1 bg-white/10 px-2 py-1 rounded-full text-[10px]">
                          <span class="h-1.5 w-1.5 rounded-full bg-orange-400 animate-pulse"></span>
                          <span>{{ summary.emi_count }} Loans</span>
                      </div>
                  </div>
                  <!-- Stats Row -->
                  <div class="grid grid-cols-4 gap-2 mt-4 pt-3 border-t border-white/10">
                      <div class="min-w-0">
                          <p class="text-[9px] text-gray-500">Monthly</p>
                          <p class="text-xs font-medium truncate">{{ formatCurrency(summary.this_month_total, currencyCode) }}</p>
                      </div>
                      <div class="min-w-0">
                          <p class="text-[9px] text-gray-500">Interest</p>
                          <p class="text-xs font-medium text-emerald-400 truncate">{{ formatCurrency(emiSummary.total_interest_paid || 0, currencyCode) }}</p>
                      </div>
                      <div class="min-w-0">
                          <p class="text-[9px] text-gray-500">Active</p>
                          <p class="text-xs font-medium">{{ summary.active_count }}</p>
                      </div>
                      <div class="min-w-0">
                          <p class="text-[9px] text-gray-500">Progress</p>
                          <p class="text-xs font-medium text-orange-300">{{ emiSummary.overall_completion || 0 }}%</p>
                      </div>
                  </div>
              </div>

              <!-- Next Payment Alert -->
              <div v-if="summary.next_payment" class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-all">
                  <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #fee2e2; color: #dc2626;">
                          <AlertCircle class="h-5 w-5" />
                      </div>
                      <div class="flex-1 min-w-0">
                          <h4 class="font-medium text-gray-900 truncate">{{ summary.next_payment.name }}</h4>
                          <p class="text-xs text-gray-500 truncate">{{ formatRelativeDate(summary.next_payment.date) }}</p>
                      </div>
                      <div class="text-right flex-shrink-0">
                          <p class="font-medium text-red-600">-{{ formatCurrency(summary.next_payment.amount, currencyCode) }}</p>
                          <p class="text-sm text-gray-400">Due</p>
                      </div>
                  </div>
              </div>
          </div>

          <!-- Loan Portfolio (only if has EMIs) -->
          <div v-if="emiSummary.loans && emiSummary.loans.length > 0" class="mt-4">
              <h3 class="text-xs font-medium text-gray-900 mb-2 px-1">Active Loans</h3>
              <div class="space-y-3">
                  <div
                      v-for="loan in emiSummary.loans"
                      :key="loan.id"
                      @click="viewLoanDetails(loan.id)"
                      class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-all"
                  >
                      <div class="flex items-center gap-3">
                          <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #fef3c7; color: #d97706;">
                              <Landmark class="h-5 w-5" />
                          </div>
                          <div class="flex-1 min-w-0">
                              <h4 class="font-medium text-gray-900 truncate">{{ loan.name }}</h4>
                              <p class="text-xs text-gray-500 truncate">{{ loan.payments_remaining }} EMIs left</p>
                          </div>
                          <div class="text-right flex-shrink-0">
                              <p class="font-medium text-gray-900">{{ formatCurrency(loan.emi_amount, currencyCode) }}</p>
                              <p class="text-sm text-gray-400">{{ loan.completion_percentage }}%</p>
                          </div>
                      </div>
                      <!-- Progress Bar -->
                      <div class="mt-3 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                          <div class="h-full bg-amber-500 rounded-full" :style="{ width: loan.completion_percentage + '%' }"></div>
                      </div>
                  </div>
              </div>
          </div>

          <!-- Suggestions -->
          <div v-if="suggestions.length > 0" class="mt-4 overflow-hidden">
              <div class="flex items-center gap-1.5 mb-2 px-1">
                  <Sparkles class="w-3 h-3 text-blue-500 flex-shrink-0" />
                  <span class="text-[10px] font-medium text-gray-600">Detected from transactions</span>
              </div>
              <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide -mx-3 px-3">
                  <button
                      v-for="suggestion in topSuggestions"
                      :key="suggestion.name"
                      @click="addFromSuggestion(suggestion)"
                      class="flex-shrink-0 flex items-center gap-2 px-3 py-2 bg-white rounded-lg shadow-sm text-xs"
                  >
                      <span class="text-gray-600 truncate max-w-[80px]">{{ suggestion.name }}</span>
                      <span class="font-medium text-gray-900">{{ formatCurrency(suggestion.amount, currencyCode) }}</span>
                      <Plus class="w-3 h-3 text-gray-400 flex-shrink-0" />
                  </button>
              </div>
          </div>
      </template>
    <!-- End New Overview Card -->

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
          class="flex flex-col items-center justify-center py-12 px-4 bg-white rounded-xl shadow-sm tab-content">
          <CalendarClock class="h-16 w-16 text-gray-300 mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">No Recurring Expenses</h3>
          <p class="text-gray-500 text-center text-sm mb-6 max-w-sm">
            Add subscriptions, bills & EMIs to track them here.
          </p>
        </div>

        <!-- Upcoming Payments Section -->
        <div v-else class="tab-content">
          <!-- Section Header with Filter Tabs -->
          <div class="mb-3 overflow-hidden">
              <div class="flex items-center justify-between mb-2">
                  <div class="min-w-0">
                      <h3 class="text-xs font-medium text-gray-900">Upcoming Payments</h3>
                      <p class="text-[10px] text-gray-500">Bills, Subscriptions & EMIs</p>
                  </div>
              </div>
              <div class="flex gap-1.5 overflow-x-auto pb-1 scrollbar-hide -mx-3 px-3">
                  <button
                      v-for="tab in expenseTypeTabs"
                      :key="tab.key"
                      @click="selectedExpenseType = tab.key"
                      class="px-2.5 py-1 rounded-full text-[10px] font-medium whitespace-nowrap transition-colors flex-shrink-0"
                      :class="selectedExpenseType === tab.key
                          ? 'bg-gray-900 text-white shadow-sm'
                          : 'bg-white border border-gray-200 text-gray-600 active:bg-gray-100'"
                  >
                      {{ tab.label }} ({{ getExpensesByType(tab.key).length }})
                  </button>
              </div>
          </div>

          <!-- Payments List -->
          <div class="space-y-3">
              <div
                  v-for="expense in filteredExpensesByType"
                  :key="expense.id"
                  @click="editRecurringExpense(expense)"
                  class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-all"
              >
                  <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                           :style="{ backgroundColor: getExpenseColor(expense) + '15', color: getExpenseColor(expense) }">
                          <component :is="getExpenseIcon(expense)" class="h-5 w-5" />
                      </div>
                      <div class="flex-1 min-w-0">
                          <h4 class="font-medium text-gray-900 truncate">{{ expense.name }}</h4>
                          <p class="text-xs text-gray-500 truncate">{{ expense.next_payment_date ? formatDate(expense.next_payment_date) : expense.payment_day + getDayOrdinal(expense.payment_day) + ' monthly' }}</p>
                      </div>
                      <div class="text-right flex-shrink-0">
                          <p class="font-medium text-gray-900">{{ formatCurrency(expense.amount, currencyCode) }}</p>
                          <p class="text-sm text-gray-400">
                              {{ getPaymentStatus(expense) }}
                          </p>
                      </div>
                  </div>
              </div>

              <!-- Empty state -->
              <div v-if="filteredExpensesByType.length === 0"
                class="flex flex-col items-center justify-center py-12 px-4 bg-white rounded-xl shadow-sm">
                  <p class="text-gray-500 text-center text-sm">No {{ selectedExpenseType === 'all' ? '' : selectedExpenseType }} expenses</p>
              </div>
          </div>
        </div>
      </template>

      <!-- Daily Transactions View -->
      <template v-else>
        <div class="tab-content">
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
                  <div v-for="transaction in expenseTransactions" :key="transaction.id"
                    class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-all cursor-pointer"
                    @click="editTransaction(transaction)">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" :style="{
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
                      <div class="text-right flex-shrink-0">
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
                  <div v-for="transaction in incomeTransactions" :key="transaction.id"
                    class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-all cursor-pointer"
                    @click="editTransaction(transaction)">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" :style="{
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
                      <div class="text-right flex-shrink-0">
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
                  <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" :style="{
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
                  <div class="text-right flex-shrink-0">
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
    </div>
    <!-- End Scrollable Content -->

    <!-- Modals (outside scrollable area) -->
    <!-- Start Desktop Model -->
    <GlobalModal v-model="showDesktopModal" :title="getActiveTab === 'daily' ? (editingTransaction ? 'Edit Transaction' : 'New Transaction') : (editingRecurring ? 'Edit Recurring Expense' : 'New Recurring Expense')" size="max-w-md">
      <template #default>
        <AddTransaction v-if="getActiveTab === 'daily'" @transaction-added="handleTransactionAdded" @remove="removeItem" :item="editingTransaction" @close="closeModal" />
        <RecurringExpenseForm v-else :editingExpense="editingRecurring" :suggestion="selectedSuggestion" :loading="saving" :showHeader="false" @save="handleRecurringExpenseSave" @delete="handleRecurringExpenseDelete" @cancel="closeModal" />
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

    <!-- Loan Detail Modal -->
    <LoanDetailModal v-model="showLoanDetailModal" :loanId="selectedLoanId" />
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
import { useNotifications } from '../composables/useNotifications';
import { iconMixin } from '../mixins/iconMixin';
import GlobalModal from './Global/GlobalModal.vue'
import TransactionsDoubleLineBarChart from './Stats/TransactionsDoubleLineBarChart.vue'
import DailyBarChart from './Stats/DailyBarChart.vue'
import ExportModal from './ExportModal.vue'
import LoanDetailModal from './LoanDetailModal.vue'
import { Wallet, CreditCard, Banknote, Download, ChevronRight, AlertCircle } from 'lucide-vue-next'
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
    LoanDetailModal,
    Wallet, CreditCard, Banknote, Download, ChevronRight, AlertCircle
  },
  setup() {
    const walletStore = useWalletStore();
    const { showValidationErrors, notify } = useNotifications();
    return { walletStore, showValidationErrors, notify };
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
      showLoanDetailModal: false,
      selectedLoanId: null,
      selectedExpenseType: 'all',
      selectedSuggestion: null,
      expenseTypeTabs: [
        { key: 'all', label: 'All' },
        { key: 'subscription', label: 'Subscriptions' },
        { key: 'bill', label: 'Bills' },
        { key: 'emi', label: 'EMIs' },
        { key: 'other', label: 'Other' }
      ],
      // Wallet scroll arrows state
      showWalletArrows: false,
      canScrollWalletsLeft: false,
      canScrollWalletsRight: false
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
    displayWallets() {
      return this.wallets.slice(0, 5)
    },

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
    expenseTransactions() {
      return this.barTransactions.filter(t => t.type === 'expense')
    },
    incomeTransactions() {
      return this.barTransactions.filter(t => t.type === 'income')
    },
    topSuggestions() {
      return this.suggestions.slice(0, 4)
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
    emiProgressPercent() {
      if (!this.summary.emi_payments_total || this.summary.emi_payments_total === 0) {
        return 0;
      }
      return Math.min(100, Math.round((this.summary.emi_payments_made / this.summary.emi_payments_total) * 100));
    },
    emiSummary() {
      return this.summary.emi_summary || {
        total_monthly_emi: 0,
        total_loan_amount: 0,
        total_payable: 0,
        total_interest_paid: 0,
        total_interest_remaining: 0,
        total_principal_paid: 0,
        total_principal_remaining: 0,
        overall_completion: 0,
        highest_interest_loan: null,
        soonest_ending_loan: null,
        loans: []
      };
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
      async handler(newValue, oldValue) {
        // Skip if this is the initial load (handled in created)
        if (oldValue === undefined) return;
        try {
          this.saving = true;
          await this.fetchTransactions(newValue);
        } catch (error) {
        } finally {
          this.saving = false;
        }
      }
    },
    async getActiveTab(newTab) {
      if (newTab === 'recurring') {
        await this.fetchRecurringExpenses()
        this.setRecurringSummary()
        // Fetch suggestions in background
        this.fetchSuggestions()
      } else if (newTab === 'daily') {
        this.initWalletScrollState();
      }
    },
    wallets: {
      handler() {
        this.initWalletScrollState();
      },
      deep: true
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
    viewLoanDetails(loanId) {
      this.selectedLoanId = loanId;
      this.showLoanDetailModal = true;
    },
    setActiveTab(tab) {
      this.activeTab = tab;
      // Update URL hash without triggering navigation
      const newHash = tab === 'recurring' ? '#recurring' : '#daily';
      if (window.location.hash !== newHash) {
        history.replaceState(null, '', newHash);
      }
    },
    initTabFromHash() {
      const hash = window.location.hash.replace('#', '').toLowerCase();
      if (hash === 'recurring' || hash === 'daily') {
        this.activeTab = hash;
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
    isPaymentDueSoon(expense) {
      if (!expense.next_payment_date) return false
      const nextDate = new Date(expense.next_payment_date)
      const today = new Date()
      const diffDays = Math.ceil((nextDate - today) / (1000 * 60 * 60 * 24))
      return diffDays <= 3 && diffDays >= 0
    },
    getPaymentStatus(expense) {
      if (!expense.next_payment_date) return 'Completed'
      const nextDate = new Date(expense.next_payment_date)
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      nextDate.setHours(0, 0, 0, 0)
      const diffDays = Math.ceil((nextDate - today) / (1000 * 60 * 60 * 24))

      if (diffDays < 0) return 'Overdue'
      if (diffDays === 0) return 'Due Today'
      if (diffDays === 1) return 'Due Tomorrow'
      if (diffDays <= 7) return `Due in ${diffDays} days`
      return 'Scheduled'
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
        this.notify({ title: 'Success', message: 'Recurring expense saved successfully', type: 'success' })
      } catch (error) {
        this.showValidationErrors(error)
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
    formatRelativeDate(date) {
      if (!date) return '';
      const targetDate = new Date(date);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      targetDate.setHours(0, 0, 0, 0);

      const diffTime = targetDate.getTime() - today.getTime();
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

      if (diffDays === 0) return 'Today';
      if (diffDays === 1) return 'Tomorrow';
      if (diffDays < 0) return `${Math.abs(diffDays)} days ago`;
      if (diffDays <= 7) return `in ${diffDays} days`;
      if (diffDays <= 30) return `in ${Math.ceil(diffDays / 7)} weeks`;
      return this.formatDate(date);
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
    getSparklinePath(data) {
      if (!data || data.length === 0) return 'M0,12 Q50,12 100,12'
      if (data.length === 1) return 'M0,12 Q50,12 100,12'

      const min = Math.min(...data)
      const max = Math.max(...data)
      const range = max - min || 1

      const points = data.map((val, i) => ({
        x: (i / (data.length - 1)) * 100,
        y: 22 - ((val - min) / range) * 20
      }))

      // Create smooth curve using cubic bezier
      let path = `M${points[0].x},${points[0].y}`

      for (let i = 0; i < points.length - 1; i++) {
        const p0 = points[Math.max(0, i - 1)]
        const p1 = points[i]
        const p2 = points[i + 1]
        const p3 = points[Math.min(points.length - 1, i + 2)]

        // Calculate control points for smooth curve
        const tension = 0.3
        const cp1x = p1.x + (p2.x - p0.x) * tension
        const cp1y = p1.y + (p2.y - p0.y) * tension
        const cp2x = p2.x - (p3.x - p1.x) * tension
        const cp2y = p2.y - (p3.y - p1.y) * tension

        path += ` C${cp1x},${cp1y} ${cp2x},${cp2y} ${p2.x},${p2.y}`
      }

      return path
    },
    getSparklineColor(data) {
      if (!data || data.length < 2) return '#9CA3AF'
      const first = data[0]
      const last = data[data.length - 1]
      if (last > first) return '#10B981' // green - up
      if (last < first) return '#EF4444' // red - down
      return '#9CA3AF' // gray - flat
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
    // Wallet scroll methods
    scrollWallets(direction) {
      const container = this.$refs.walletsContainer;
      if (!container) return;

      const scrollAmount = 200;
      const targetScroll = direction === 'left'
        ? container.scrollLeft - scrollAmount
        : container.scrollLeft + scrollAmount;

      container.scrollTo({
        left: targetScroll,
        behavior: 'smooth'
      });
    },
    updateWalletScrollState() {
      const container = this.$refs.walletsContainer;
      if (!container) return;

      this.canScrollWalletsLeft = container.scrollLeft > 0;
      this.canScrollWalletsRight = container.scrollLeft < (container.scrollWidth - container.clientWidth - 1);
    },
    initWalletScrollState() {
      this.$nextTick(() => {
        this.updateWalletScrollState();
      });
    },
  },
  async created() {
    // Initialize tab from URL hash
    this.initTabFromHash();

    // Listen for hash changes (browser back/forward)
    window.addEventListener('hashchange', this.initTabFromHash);

    // Listen for nav add button click
    window.addEventListener('nav-add-clicked', this.createNewTransaction);

    try {
      const walletStore = useWalletStore();
      const store = useTransactionStore();

      // Fetch all essential data in parallel for fastest load
      await Promise.all([
        this.fetchSettings(),
        walletStore.wallets.length === 0 ? this.fetchWallets() : Promise.resolve(),
        this.fetchTransactions(this.dateFilter),
        store.fetchDailyBarData()
      ]);

      // Fetch bar transactions for selected day (non-blocking)
      if (store.selectedDayBar) {
        store.fetchBarTransactions('D', [store.selectedDayBar.date, store.selectedDayBar.date]);
      }

      // Fetch recurring expenses only if on recurring tab (lazy load)
      if (this.getActiveTab === 'recurring') {
        await this.fetchRecurringExpenses();
        this.setRecurringSummary();
        this.fetchSuggestions();
      }
    } catch (error) {
      console.error('Error loading data:', error);
    }
  },
  mounted() {
    // Initialize wallet scroll state after DOM is ready
    this.initWalletScrollState();
  },
  beforeUnmount() {
    // Clean up listeners
    window.removeEventListener('hashchange', this.initTabFromHash);
    window.removeEventListener('nav-add-clicked', this.createNewTransaction);
  }
}
</script>

<style scoped>
/* Hide scrollbar but allow scroll */
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

/* Tab content animation */
.tab-content {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Switch Tabs */
.switch-container {
  display: inline-flex;
  position: relative;
  background: white;
  border-radius: 9999px;
  padding: 3px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.switch-indicator {
  position: absolute;
  top: 3px;
  bottom: 3px;
  left: 3px;
  width: calc(50% - 3px);
  background: #1f2937;
  border-radius: 9999px;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.switch-indicator.switch-right {
  transform: translateX(100%);
}

.switch-btn {
  position: relative;
  z-index: 10;
  padding: 6px 16px;
  font-size: 12px;
  font-weight: 500;
  color: #6b7280;
  background: transparent;
  border: none;
  border-radius: 9999px;
  cursor: pointer;
  transition: color 0.3s ease;
  min-width: 70px;
  text-align: center;
}

.switch-btn:active {
  transform: scale(0.95);
}

.switch-btn.active {
  color: white;
}

.switch-btn:not(.active):hover {
  color: #374151;
}

@media (min-width: 768px) {
  .switch-container {
    padding: 4px;
  }

  .switch-indicator {
    top: 4px;
    bottom: 4px;
    left: 4px;
    width: calc(50% - 4px);
  }

  .switch-btn {
    padding: 8px 24px;
    font-size: 13px;
    min-width: 90px;
  }
}

/* Wallet cards - 3 on mobile, 5 on desktop */
.wallet-card {
  width: calc((100% - 16px) / 3); /* 3 cards with 2 gaps of 8px */
  min-width: calc((100% - 16px) / 3);
}

@media (min-width: 768px) {
  .wallet-card {
    width: calc((100% - 32px) / 5); /* 5 cards with 4 gaps of 8px */
    min-width: calc((100% - 32px) / 5);
  }
}
</style>
