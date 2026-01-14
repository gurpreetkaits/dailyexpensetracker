<template>
  <div class="bg-white rounded-xl shadow-sm p-4 h-full">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-xl font-semibold text-blue-600">{{ formatCurrency(summary.this_month_total, currencyCode) }}</h2>
        <p class="text-sm text-gray-500">Monthly Recurring</p>
      </div>
      <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
        <RefreshCw class="h-5 w-5 text-blue-600" />
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 gap-3 mb-4">
      <div class="bg-purple-50 rounded-lg p-3">
        <div class="flex items-center gap-2 mb-1">
          <Tv class="h-4 w-4 text-purple-600" />
          <span class="text-xs text-gray-500">Subscriptions</span>
        </div>
        <p class="text-lg font-semibold text-purple-600">{{ summary.subscription_count }}</p>
      </div>
      <div class="bg-orange-50 rounded-lg p-3">
        <div class="flex items-center gap-2 mb-1">
          <Receipt class="h-4 w-4 text-orange-600" />
          <span class="text-xs text-gray-500">Bills</span>
        </div>
        <p class="text-lg font-semibold text-orange-600">{{ summary.bill_count }}</p>
      </div>
      <div class="bg-red-50 rounded-lg p-3">
        <div class="flex items-center gap-2 mb-1">
          <CreditCard class="h-4 w-4 text-red-600" />
          <span class="text-xs text-gray-500">EMIs/Loans</span>
        </div>
        <p class="text-lg font-semibold text-red-600">{{ summary.emi_count }}</p>
      </div>
      <div class="bg-gray-50 rounded-lg p-3">
        <div class="flex items-center gap-2 mb-1">
          <MoreHorizontal class="h-4 w-4 text-gray-600" />
          <span class="text-xs text-gray-500">Other</span>
        </div>
        <p class="text-lg font-semibold text-gray-600">{{ summary.other_count }}</p>
      </div>
    </div>

    <!-- Donut Chart -->
    <div v-if="!loading && hasData" class="h-[250px]">
      <v-chart :option="chartOption" autoresize />
    </div>
    <div v-else-if="loading" class="h-[250px] flex items-center justify-center">
      <div class="text-gray-400">Loading...</div>
    </div>
    <div v-else class="h-[250px] flex items-center justify-center">
      <div class="text-center text-gray-400">
        <RefreshCw class="h-8 w-8 mx-auto mb-2 opacity-50" />
        <p class="text-sm">No recurring expenses</p>
      </div>
    </div>

    <!-- EMI Progress (if any EMIs) -->
    <div v-if="summary.emi_count > 0" class="mt-4 pt-4 border-t border-gray-100">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-gray-700">EMI Progress</span>
        <span class="text-sm text-gray-500">{{ summary.emi_payments_made }}/{{ summary.emi_payments_total }} payments</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2.5">
        <div
          class="bg-gradient-to-r from-blue-500 to-emerald-500 h-2.5 rounded-full transition-all duration-500"
          :style="{ width: emiProgress + '%' }"
        ></div>
      </div>
      <div class="flex justify-between mt-2 text-xs text-gray-500">
        <span>{{ emiProgress }}% complete</span>
        <span>{{ formatCurrency(summary.total_remaining_balance, currencyCode) }} remaining</span>
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
import { RefreshCw, Tv, Receipt, CreditCard, MoreHorizontal } from 'lucide-vue-next'
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
    MoreHorizontal
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
        data.push({ name: 'Subscriptions', value: total, itemStyle: { color: '#9333ea' } })
      }
      if (this.grouped.bills?.length > 0) {
        const total = this.grouped.bills.reduce((sum, item) => sum + parseFloat(item.amount), 0)
        data.push({ name: 'Bills', value: total, itemStyle: { color: '#ea580c' } })
      }
      if (this.grouped.emis?.length > 0) {
        const total = this.grouped.emis.reduce((sum, item) => sum + parseFloat(item.amount), 0)
        data.push({ name: 'EMIs', value: total, itemStyle: { color: '#dc2626' } })
      }
      if (this.grouped.other?.length > 0) {
        const total = this.grouped.other.reduce((sum, item) => sum + parseFloat(item.amount), 0)
        data.push({ name: 'Other', value: total, itemStyle: { color: '#6b7280' } })
      }

      return data
    },
    chartOption() {
      return {
        tooltip: {
          trigger: 'item',
          formatter: (params) => {
            return `${params.name}: ${this.formatCurrency(params.value, this.currencyCode)} (${params.percent}%)`
          }
        },
        legend: {
          orient: 'horizontal',
          bottom: 0,
          itemWidth: 12,
          itemHeight: 12,
          textStyle: {
            fontSize: 11
          }
        },
        series: [
          {
            name: 'Recurring Expenses',
            type: 'pie',
            radius: ['45%', '70%'],
            center: ['50%', '45%'],
            avoidLabelOverlap: true,
            itemStyle: {
              borderRadius: 6,
              borderColor: '#fff',
              borderWidth: 2
            },
            label: {
              show: false
            },
            emphasis: {
              label: {
                show: true,
                fontSize: 12,
                fontWeight: 'bold'
              },
              itemStyle: {
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowColor: 'rgba(0, 0, 0, 0.2)'
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
