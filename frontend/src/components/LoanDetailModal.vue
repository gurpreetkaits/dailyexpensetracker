<template>
  <GlobalModal v-model="isOpen" title="Loan Details" size="max-w-2xl">
    <template #default>
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-500"></div>
      </div>

      <div v-else-if="loan" class="space-y-4 max-h-[70vh] overflow-y-auto">
        <!-- Loan Header -->
        <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-xl p-4 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-amber-100 text-sm">{{ loan.name }}</p>
              <p class="text-2xl font-bold">{{ formatCurrency(loan.emi_amount, currencyCode) }}<span class="text-sm font-normal">/month</span></p>
            </div>
            <div class="text-right">
              <p class="text-amber-100 text-sm">Interest Rate</p>
              <p class="text-xl font-bold">{{ loan.interest_rate }}%</p>
            </div>
          </div>
        </div>

        <!-- Progress Section -->
        <div class="bg-white rounded-xl p-4 border border-gray-100">
          <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-gray-700">Loan Progress</span>
            <span class="text-sm font-bold text-amber-600">{{ loan.completion_percentage }}%</span>
          </div>
          <div class="w-full bg-gray-100 rounded-full h-3 mb-3">
            <div class="bg-gradient-to-r from-amber-400 to-amber-500 h-3 rounded-full transition-all"
                 :style="{ width: loan.completion_percentage + '%' }"></div>
          </div>
          <div class="flex justify-between text-xs text-gray-500">
            <span>{{ loan.payments_completed }} payments done</span>
            <span>{{ loan.payments_remaining }} remaining</span>
          </div>
        </div>

        <!-- Key Metrics Grid -->
        <div class="grid grid-cols-2 gap-3">
          <!-- Principal -->
          <div class="bg-gray-50 rounded-xl p-3">
            <p class="text-xs text-gray-500 mb-1">Loan Amount</p>
            <p class="text-lg font-bold text-gray-900">{{ formatCurrency(loan.principal_amount, currencyCode) }}</p>
          </div>
          <!-- Total Interest -->
          <div class="bg-gray-50 rounded-xl p-3">
            <p class="text-xs text-gray-500 mb-1">Total Interest</p>
            <p class="text-lg font-bold text-red-600">{{ formatCurrency(loan.total_interest, currencyCode) }}</p>
          </div>
          <!-- Total Payable -->
          <div class="bg-gray-50 rounded-xl p-3">
            <p class="text-xs text-gray-500 mb-1">Total Payable</p>
            <p class="text-lg font-bold text-gray-900">{{ formatCurrency(loan.total_payable, currencyCode) }}</p>
          </div>
          <!-- Tenure -->
          <div class="bg-gray-50 rounded-xl p-3">
            <p class="text-xs text-gray-500 mb-1">Tenure</p>
            <p class="text-lg font-bold text-gray-900">{{ loan.tenure_months }} months</p>
          </div>
        </div>

        <!-- Paid vs Remaining -->
        <div class="bg-white rounded-xl p-4 border border-gray-100">
          <h4 class="text-sm font-medium text-gray-700 mb-3">Payment Breakdown</h4>

          <!-- Principal Breakdown -->
          <div class="mb-4">
            <div class="flex justify-between text-xs mb-1">
              <span class="text-gray-600">Principal</span>
              <span class="text-gray-900 font-medium">{{ formatCurrency(loan.principal_paid, currencyCode) }} / {{ formatCurrency(loan.principal_amount, currencyCode) }}</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
              <div class="bg-blue-500 h-2 rounded-full" :style="{ width: principalProgress + '%' }"></div>
            </div>
          </div>

          <!-- Interest Breakdown -->
          <div>
            <div class="flex justify-between text-xs mb-1">
              <span class="text-gray-600">Interest</span>
              <span class="text-red-600 font-medium">{{ formatCurrency(loan.interest_paid, currencyCode) }} / {{ formatCurrency(loan.total_interest, currencyCode) }}</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
              <div class="bg-red-400 h-2 rounded-full" :style="{ width: interestProgress + '%' }"></div>
            </div>
          </div>
        </div>

        <!-- Current Month EMI Breakdown -->
        <div class="bg-amber-50 rounded-xl p-4">
          <h4 class="text-sm font-medium text-amber-800 mb-3">This Month's EMI Breakdown</h4>
          <div class="flex items-center gap-4">
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <span class="text-xs text-amber-700">Principal</span>
                <span class="text-sm font-semibold text-amber-900">{{ formatCurrency(loan.current_month_principal, currencyCode) }}</span>
              </div>
              <div class="w-full bg-amber-200 rounded-full h-1.5 mt-1">
                <div class="bg-blue-500 h-1.5 rounded-full" :style="{ width: currentMonthPrincipalPercent + '%' }"></div>
              </div>
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <span class="text-xs text-amber-700">Interest</span>
                <span class="text-sm font-semibold text-red-600">{{ formatCurrency(loan.current_month_interest, currencyCode) }}</span>
              </div>
              <div class="w-full bg-amber-200 rounded-full h-1.5 mt-1">
                <div class="bg-red-400 h-1.5 rounded-full" :style="{ width: currentMonthInterestPercent + '%' }"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Key Dates -->
        <div class="flex items-center gap-3">
          <div class="flex-1 bg-green-50 rounded-xl p-3 text-center">
            <p class="text-xs text-green-600 mb-1">Start Date</p>
            <p class="text-sm font-semibold text-green-800">{{ formatDate(loan.first_payment_date) }}</p>
          </div>
          <div class="flex-1 bg-blue-50 rounded-xl p-3 text-center">
            <p class="text-xs text-blue-600 mb-1">End Date</p>
            <p class="text-sm font-semibold text-blue-800">{{ formatDate(loan.loan_end_date) }}</p>
          </div>
          <div class="flex-1 bg-purple-50 rounded-xl p-3 text-center">
            <p class="text-xs text-purple-600 mb-1">Outstanding</p>
            <p class="text-sm font-semibold text-purple-800">{{ formatCurrency(loan.principal_remaining, currencyCode) }}</p>
          </div>
        </div>

        <!-- Amortization Schedule -->
        <div class="bg-white rounded-xl border border-gray-100">
          <button @click="showAmortization = !showAmortization"
                  class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
            <span class="text-sm font-medium text-gray-700">Amortization Schedule</span>
            <ChevronDown class="h-4 w-4 text-gray-400 transition-transform" :class="{ 'rotate-180': showAmortization }" />
          </button>

          <div v-if="showAmortization" class="border-t border-gray-100 max-h-64 overflow-y-auto">
            <table class="w-full text-xs">
              <thead class="bg-gray-50 sticky top-0">
                <tr>
                  <th class="px-3 py-2 text-left text-gray-600">#</th>
                  <th class="px-3 py-2 text-left text-gray-600">Date</th>
                  <th class="px-3 py-2 text-right text-gray-600">EMI</th>
                  <th class="px-3 py-2 text-right text-gray-600">Principal</th>
                  <th class="px-3 py-2 text-right text-gray-600">Interest</th>
                  <th class="px-3 py-2 text-right text-gray-600">Balance</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in amortization" :key="row.payment_number"
                    class="border-b border-gray-50"
                    :class="{ 'bg-green-50': row.is_paid }">
                  <td class="px-3 py-2 text-gray-600">
                    <span class="flex items-center gap-1">
                      {{ row.payment_number }}
                      <CheckCircle2 v-if="row.is_paid" class="h-3 w-3 text-green-500" />
                    </span>
                  </td>
                  <td class="px-3 py-2 text-gray-600">{{ formatShortDate(row.date) }}</td>
                  <td class="px-3 py-2 text-right font-medium text-gray-900">{{ formatCurrency(row.emi, currencyCode) }}</td>
                  <td class="px-3 py-2 text-right text-blue-600">{{ formatCurrency(row.principal, currencyCode) }}</td>
                  <td class="px-3 py-2 text-right text-red-500">{{ formatCurrency(row.interest, currencyCode) }}</td>
                  <td class="px-3 py-2 text-right text-gray-600">{{ formatCurrency(row.balance, currencyCode) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Interest Summary -->
        <div class="bg-red-50 rounded-xl p-4">
          <div class="flex items-center gap-2 mb-2">
            <TrendingUp class="h-4 w-4 text-red-500" />
            <span class="text-sm font-medium text-red-800">Interest Impact</span>
          </div>
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <p class="text-xs text-red-600">Interest Paid</p>
              <p class="font-semibold text-red-800">{{ formatCurrency(loan.interest_paid, currencyCode) }}</p>
            </div>
            <div>
              <p class="text-xs text-red-600">Interest Remaining</p>
              <p class="font-semibold text-red-800">{{ formatCurrency(loan.interest_remaining, currencyCode) }}</p>
            </div>
            <div class="col-span-2">
              <p class="text-xs text-red-600">Interest as % of Principal</p>
              <p class="font-semibold text-red-800">{{ interestPercentOfPrincipal }}%</p>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-12 text-gray-500">
        <p>Unable to load loan details</p>
      </div>
    </template>
  </GlobalModal>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useRecurringExpenseStore } from '../store/recurringExpense'
import { useSettingsStore } from '../store/settings'
import { numberMixin } from '../mixins/numberMixin'
import GlobalModal from './Global/GlobalModal.vue'
import { ChevronDown, CheckCircle2, TrendingUp } from 'lucide-vue-next'

export default {
  name: 'LoanDetailModal',
  mixins: [numberMixin],
  components: {
    GlobalModal,
    ChevronDown,
    CheckCircle2,
    TrendingUp
  },
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    loanId: {
      type: [Number, String],
      default: null
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      showAmortization: false
    }
  },
  computed: {
    ...mapState(useRecurringExpenseStore, ['selectedLoanDetails', 'selectedLoanAmortization', 'loadingLoanDetails']),
    ...mapState(useSettingsStore, ['currencyCode']),
    isOpen: {
      get() {
        return this.modelValue
      },
      set(value) {
        this.$emit('update:modelValue', value)
        if (!value) {
          this.clearLoanDetails()
        }
      }
    },
    loading() {
      return this.loadingLoanDetails
    },
    loan() {
      return this.selectedLoanDetails
    },
    amortization() {
      return this.selectedLoanAmortization
    },
    principalProgress() {
      if (!this.loan || !this.loan.principal_amount) return 0
      return Math.min(100, (this.loan.principal_paid / this.loan.principal_amount) * 100)
    },
    interestProgress() {
      if (!this.loan || !this.loan.total_interest) return 0
      return Math.min(100, (this.loan.interest_paid / this.loan.total_interest) * 100)
    },
    currentMonthPrincipalPercent() {
      if (!this.loan || !this.loan.emi_amount) return 0
      return (this.loan.current_month_principal / this.loan.emi_amount) * 100
    },
    currentMonthInterestPercent() {
      if (!this.loan || !this.loan.emi_amount) return 0
      return (this.loan.current_month_interest / this.loan.emi_amount) * 100
    },
    interestPercentOfPrincipal() {
      if (!this.loan || !this.loan.principal_amount) return 0
      return ((this.loan.total_interest / this.loan.principal_amount) * 100).toFixed(1)
    }
  },
  watch: {
    loanId: {
      immediate: true,
      handler(newId) {
        if (newId && this.modelValue) {
          this.fetchLoanDetails(newId)
        }
      }
    },
    modelValue(isOpen) {
      if (isOpen && this.loanId) {
        this.fetchLoanDetails(this.loanId)
      }
    }
  },
  methods: {
    ...mapActions(useRecurringExpenseStore, ['fetchLoanDetails', 'clearLoanDetails']),
    formatDate(date) {
      if (!date) return '-'
      return new Date(date).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    },
    formatShortDate(date) {
      if (!date) return '-'
      return new Date(date).toLocaleDateString('en-IN', {
        year: '2-digit',
        month: 'short'
      })
    }
  }
}
</script>
