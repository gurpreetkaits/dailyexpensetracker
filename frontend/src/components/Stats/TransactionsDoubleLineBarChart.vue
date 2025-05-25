<template>
  <div>
    <div class="flex items-center gap-2 mb-4">
      <button
        v-for="tab in periodTabs"
        :key="tab.value"
        @click="$emit('period-change', tab.value)"
        class="px-2 py-0.5 rounded-full text-xs font-medium transition-all"
        :class="period === tab.value ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
        style="min-width: 2.5rem;"
      >
        {{ tab.label }}
      </button>
    </div>
    <v-chart :option="option" autoresize style="height: 300px; width: 100%;" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { use } from 'echarts/core'
import VChart from 'vue-echarts'
import { BarChart } from 'echarts/charts'
import { GridComponent, TooltipComponent, LegendComponent } from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'

use([BarChart, GridComponent, TooltipComponent, LegendComponent, CanvasRenderer])

const props = defineProps({
  chartData: {
    type: Array,
    default: () => []
  },
  currencyCode: {
    type: String,
    default: 'INR'
  },
  period: {
    type: String,
    default: 'D'
  },
  selectedBar: {
    type: Object,
    default: null
  }
})

const periodTabs = [
  { label: 'D', value: 'D' },
  { label: 'W', value: 'W' },
  { label: 'M', value: 'M' },
  { label: 'Y', value: 'Y' },
  { label: 'ALL', value: 'ALL' }
]

const option = computed(() => {
  const categories = props.chartData.map(bar => bar.label)
  const income = props.chartData.map(bar => bar.income)
  const expense = props.chartData.map(bar => bar.expense)

  return {
    tooltip: { trigger: 'axis' },
    legend: { data: ['Income', 'Expense'] },
    grid: { left: 20, right: 20, bottom: 40, top: 40, containLabel: true },
    xAxis: { type: 'category', data: categories },
    yAxis: { type: 'value' },
    series: [
      {
        name: 'Income',
        type: 'bar',
        data: income,
        itemStyle: { color: '#22c55e' },
        barGap: 0
      },
      {
        name: 'Expense',
        type: 'bar',
        data: expense,
        itemStyle: { color: '#ef4444' }
      }
    ]
  }
})
</script>
