<template>
  <div class="bg-white rounded-xl shadow-sm p-4 h-full flex flex-col">
    <div class="flex items-center justify-between mb-2">
      <h3 class="text-base font-semibold">Activity</h3>
      <select v-model="selectedPeriod" class="text-xs bg-gray-50 border border-gray-100 rounded-lg px-2 py-1 text-gray-600">
        <option value="week">D</option>
        <option value="month">M</option>
        <option value="year">Y</option>
      </select>
    </div>
    <div class="flex-1 min-h-[180px]">
      <v-chart :option="chartOption" autoresize />
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue'
import VChart from 'vue-echarts'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { BarChart, LineChart } from 'echarts/charts'
import { GridComponent, TooltipComponent, LegendComponent } from 'echarts/components'

use([CanvasRenderer, BarChart, LineChart, GridComponent, TooltipComponent, LegendComponent])

export default {
  name: 'TransactionsDoubleLineBarChart',
  components: { VChart },
  props: {
    chartData: {
      type: Array,
      required: true
    },
    currencyCode: {
      type: String,
      required: true
    }
  },
  setup(props) {
    const selectedPeriod = ref('month')

    const chartOption = computed(() => {
      const categories = props.chartData.map(item => item.label)
      const incomeData = props.chartData.map(item => item.income)
      const expenseData = props.chartData.map(item => item.expense)
      return {
        tooltip: {
          trigger: 'axis',
          formatter: params => {
            const [income, expense] = params
            return `
              <b>${income.axisValue}</b><br/>
              <span style='color:#10b981'>Income:</span> ${formatCurrency(income.data, props.currencyCode)}<br/>
              <span style='color:#ef4444'>Expense:</span> ${formatCurrency(expense.data, props.currencyCode)}
            `
          }
        },
        legend: {
          data: ['Income', 'Expense']
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '8%',
          containLabel: true
        },
        xAxis: {
          type: 'category',
          data: categories,
          axisLabel: { fontSize: 11 }
        },
        yAxis: {
          type: 'value',
          axisLabel: {
            formatter: value => formatCurrency(value, props.currencyCode)
          }
        },
        series: [
          {
            name: 'Income',
            type: 'bar',
            data: incomeData,
            itemStyle: { color: '#4CAF51' + 80 }
          },
          {
            name: 'Expense',
            type: 'bar',
            data: expenseData,
            itemStyle: { color: '#ef4444' + 80 }
          }
        ]
      }
    })

    function formatCurrency(amount, currency) {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency || 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(amount)
    }

    return { selectedPeriod, chartOption }
  }
}
</script>
