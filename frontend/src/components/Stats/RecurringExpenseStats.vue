<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-full">
    <!-- Header -->
    <div class="p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-gray-400 text-[10px] font-medium mb-1">Monthly Recurring</p>
          <h2 class="text-xl font-semibold text-gray-900">{{ formatCurrency(summary.this_month_total, currencyCode) }}</h2>
        </div>
        <div class="h-9 w-9 bg-blue-50 rounded-lg flex items-center justify-center">
          <RefreshCw class="h-4 w-4 text-blue-400" />
        </div>
      </div>

      <!-- Active count badge -->
      <div class="mt-2 inline-flex items-center gap-1.5 bg-gray-100 rounded-full px-2.5 py-1">
        <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
        <span class="text-gray-500 text-[10px] font-medium">{{ summary.active_count }} active</span>
      </div>
    </div>

    <div class="p-4">
      <!-- Stats Grid -->
      <div class="grid grid-cols-4 gap-2 mb-4">
        <div class="bg-gray-50 rounded-xl p-3 text-center hover:bg-gray-100 transition-colors cursor-default">
          <div class="w-8 h-8 mx-auto mb-2 bg-white rounded-lg flex items-center justify-center shadow-sm">
            <Tv class="h-4 w-4 text-gray-600" />
          </div>
          <p class="text-xl font-bold text-gray-800">{{ summary.subscription_count }}</p>
          <p class="text-[10px] text-gray-500 font-medium">Subscriptions</p>
        </div>

        <div class="bg-gray-50 rounded-xl p-3 text-center hover:bg-gray-100 transition-colors cursor-default">
          <div class="w-8 h-8 mx-auto mb-2 bg-white rounded-lg flex items-center justify-center shadow-sm">
            <Receipt class="h-4 w-4 text-gray-600" />
          </div>
          <p class="text-xl font-bold text-gray-800">{{ summary.bill_count }}</p>
          <p class="text-[10px] text-gray-500 font-medium">Bills</p>
        </div>

        <div class="bg-gray-50 rounded-xl p-3 text-center hover:bg-gray-100 transition-colors cursor-default">
          <div class="w-8 h-8 mx-auto mb-2 bg-white rounded-lg flex items-center justify-center shadow-sm">
            <CreditCard class="h-4 w-4 text-gray-600" />
          </div>
          <p class="text-xl font-bold text-gray-800">{{ summary.emi_count }}</p>
          <p class="text-[10px] text-gray-500 font-medium">EMIs</p>
        </div>

        <div class="bg-gray-50 rounded-xl p-3 text-center hover:bg-gray-100 transition-colors cursor-default">
          <div class="w-8 h-8 mx-auto mb-2 bg-white rounded-lg flex items-center justify-center shadow-sm">
            <MoreHorizontal class="h-4 w-4 text-gray-600" />
          </div>
          <p class="text-xl font-bold text-gray-800">{{ summary.other_count }}</p>
          <p class="text-[10px] text-gray-500 font-medium">Other</p>
        </div>
      </div>

      <!-- Donut Chart -->
      <div v-if="!loading && hasData" class="h-[200px] relative">
        <v-chart :option="chartOption" autoresize />
      </div>
      <div v-else-if="loading" class="h-[200px] flex items-center justify-center">
        <div class="flex flex-col items-center gap-2">
          <div class="h-8 w-8 border-2 border-gray-300 border-t-transparent rounded-full animate-spin"></div>
          <p class="text-gray-400 text-sm">Loading...</p>
        </div>
      </div>
      <div v-else class="h-[200px] flex items-center justify-center">
        <div class="text-center">
          <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 rounded-2xl flex items-center justify-center">
            <RefreshCw class="h-8 w-8 text-gray-300" />
          </div>
          <p class="text-sm font-medium text-gray-400">No recurring expenses</p>
          <p class="text-xs text-gray-300 mt-1">Add subscriptions, bills or EMIs</p>
        </div>
      </div>

      <!-- EMI Progress (if any EMIs) -->
      <div v-if="summary.emi_count > 0" class="mt-4 pt-4 border-t border-gray-100">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2">
            <div class="h-8 w-8 bg-gray-100 rounded-lg flex items-center justify-center">
              <TrendingUp class="h-4 w-4 text-gray-600" />
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-800">EMI Progress</p>
              <p class="text-xs text-gray-400">{{ summary.emi_payments_made }}/{{ summary.emi_payments_total }} payments</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-lg font-bold text-gray-800">{{ emiProgress }}%</p>
          </div>
        </div>

        <div class="relative w-full h-2.5 bg-gray-100 rounded-full overflow-hidden">
          <div
            class="absolute inset-y-0 left-0 bg-gray-800 rounded-full transition-all duration-700 ease-out"
            :style="{ width: emiProgress + '%' }"
          ></div>
        </div>

        <div class="flex justify-between mt-2">
          <span class="text-xs text-gray-400">Paid</span>
          <span class="text-xs font-medium text-gray-600">{{ formatCurrency(summary.total_remaining_balance, currencyCode) }} remaining</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { PieChart } from 'echarts/charts'
import { TooltipComponent, LegendComponent } from 'echarts/components'
import VChart from 'vue-echarts'
import { RefreshCw, Tv, Receipt, CreditCard, MoreHorizontal, TrendingUp } from 'lucide-vue-next'
import { numberMixin } from '../../mixins/numberMixin'
import { mapState } from 'pinia'
import { useRecurringExpenseStore } from '../../store/recurringExpense'

use([CanvasRenderer, PieChart, TooltipComponent, LegendComponent])

export default {
  name: 'RecurringExpenseStats',
  components: {
    VChart,
    RefreshCw,
    Tv,
    Receipt,
    CreditCard,
    MoreHorizontal,
    TrendingUp
  },
  mixins: [numberMixin],
  props: {
    currencyCode: {
      type: String,
      required: true
    }
  },
  computed: {
    ...mapState(useRecurringExpenseStore, ['summary', 'grouped', 'loading']),
    hasData() {
      return this.summary.active_count > 0
    },
    emiProgress() {
      if (this.summary.emi_payments_total === 0) return 0
      return Math.round((this.summary.emi_payments_made / this.summary.emi_payments_total) * 100)
    },
    chartData() {
      const data = []

      if (this.grouped.subscriptions?.length > 0) {
        const total = this.grouped.subscriptions.reduce((sum, item) => sum + parseFloat(item.amount), 0)
        data.push({ name: 'Subscriptions', value: total, itemStyle: { color: '#93c5fd' } })
      }
      if (this.grouped.bills?.length > 0) {
        const total = this.grouped.bills.reduce((sum, item) => sum + parseFloat(item.amount), 0)
        data.push({ name: 'Bills', value: total, itemStyle: { color: '#fcd34d' } })
      }
      if (this.grouped.emis?.length > 0) {
        const total = this.grouped.emis.reduce((sum, item) => sum + parseFloat(item.amount), 0)
        data.push({ name: 'EMIs', value: total, itemStyle: { color: '#fca5a5' } })
      }
      if (this.grouped.other?.length > 0) {
        const total = this.grouped.other.reduce((sum, item) => sum + parseFloat(item.amount), 0)
        data.push({ name: 'Other', value: total, itemStyle: { color: '#d1d5db' } })
      }

      return data
    },
    chartOption() {
      return {
        tooltip: {
          trigger: 'item',
          backgroundColor: 'rgba(255, 255, 255, 0.95)',
          borderColor: '#e5e7eb',
          borderWidth: 1,
          textStyle: {
            color: '#374151'
          },
          formatter: (params) => {
            return `<div class="font-medium">${params.name}</div><div class="text-lg font-bold">${this.formatCurrency(params.value, this.currencyCode)}</div><div class="text-gray-500">${params.percent}%</div>`
          }
        },
        legend: {
          orient: 'horizontal',
          bottom: 0,
          itemWidth: 10,
          itemHeight: 10,
          itemGap: 16,
          textStyle: {
            fontSize: 11,
            color: '#6b7280'
          }
        },
        series: [
          {
            name: 'Recurring Expenses',
            type: 'pie',
            radius: ['50%', '75%'],
            center: ['50%', '45%'],
            avoidLabelOverlap: true,
            itemStyle: {
              borderRadius: 8,
              borderColor: '#fff',
              borderWidth: 3
            },
            label: {
              show: false
            },
            emphasis: {
              scale: true,
              scaleSize: 8,
              itemStyle: {
                shadowBlur: 20,
                shadowOffsetX: 0,
                shadowColor: 'rgba(0, 0, 0, 0.15)'
              }
            },
            data: this.chartData
          }
        ]
      }
    }
  },
  async mounted() {
    const store = useRecurringExpenseStore()
    if (store.recurringExpenses.length === 0) {
      await store.fetchRecurringExpenses()
    }
  }
}
</script>

<style scoped>
@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}
.animate-shimmer {
  animation: shimmer 2s infinite;
}
</style>
