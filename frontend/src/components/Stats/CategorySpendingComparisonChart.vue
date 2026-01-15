<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Header -->
    <div class="p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-gray-400 text-[10px] font-medium mb-1">Year over Year</p>
          <h3 class="text-base font-semibold text-gray-900">Spending Comparison</h3>
        </div>

        <!-- Year Selectors -->
        <div class="flex items-center gap-1.5">
          <select v-model="selectedPreviousYear"
            class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-600 border-0 focus:ring-2 focus:ring-gray-200 cursor-pointer">
            <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
          </select>
          <span class="text-gray-300 text-xs">vs</span>
          <select v-model="selectedCurrentYear"
            class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-600 border-0 focus:ring-2 focus:ring-gray-200 cursor-pointer">
            <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Chart -->
    <div class="p-4">
      <v-chart :option="option" style="height: 300px;" autoresize />
    </div>
  </div>
</template>

<script>
import VChart from 'vue-echarts'
import { useStatsStore } from '../../store/stats'
import { mapActions, mapState } from 'pinia'
// import axios from 'axios'

export default {
  name: 'CategorySpendingComparisonChart',
  components: { VChart },
  props: {
    filter: {
      type: String,
      default: 'month'
    },
    getFilters: {
      type: Object,
      required: true
    },
    currencyCode: {
      type: String,
      required: true
    },
    formatCurrency: {
      type: Function,
      required: true
    }
  },
  data() {
    const currentYear = new Date().getFullYear()
    return {
      availableYears: [currentYear, currentYear - 1, currentYear - 2, currentYear - 3],
      selectedPreviousYear: currentYear - 1,
      selectedCurrentYear: currentYear,
      option: {
        title: { text: '' },
        tooltip: { trigger: 'axis' },
        legend: { data: [] },
        xAxis: { type: 'category', data: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] },
        yAxis: { type: 'value' },
        series: []
      }
    }
  },
  computed: {
    ...mapState(useStatsStore, ['currentYearData', 'previousYearData'])
  },
  watch: {
    selectedPreviousYear: 'fetchMonthlySpendings',
    selectedCurrentYear: 'fetchMonthlySpendings',
    currentYearData: 'updateChart',
    previousYearData: 'updateChart'
  },
  methods: {
    ...mapActions(useStatsStore, ['getYearlyComparison']),
    async fetchMonthlySpendings() {
      if (this.selectedPreviousYear === this.selectedCurrentYear) {
        this.option = { ...this.option, series: [], legend: { data: [] } }
        return
      }
      await this.getYearlyComparison(this.selectedPreviousYear, this.selectedCurrentYear)
      // updateChart will be called by watcher
    },
    updateChart() {
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
      const previousYear = Array(12).fill(0)
      const currentYear = Array(12).fill(0)

      if (Array.isArray(this.previousYearData)) {
        this.previousYearData.forEach(m => {
          const idx = months.indexOf(m.month)
          if (idx !== -1) previousYear[idx] = m.amount
        })
      }
      if (Array.isArray(this.currentYearData)) {
        this.currentYearData.forEach(m => {
          const idx = months.indexOf(m.month)
          if (idx !== -1) currentYear[idx] = m.amount
        })
      }

      this.option = {
        title: { text: '' },
        tooltip: {
          trigger: 'axis',
          backgroundColor: 'rgba(255, 255, 255, 0.95)',
          borderColor: '#e5e7eb',
          borderWidth: 1,
          textStyle: { color: '#374151' },
          axisPointer: {
            type: 'cross',
            crossStyle: { color: '#999' }
          }
        },
        legend: {
          data: [String(this.selectedPreviousYear), String(this.selectedCurrentYear)],
          bottom: 0,
          itemWidth: 12,
          itemHeight: 12,
          textStyle: { fontSize: 12, color: '#6b7280' }
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '15%',
          top: '10%',
          containLabel: true
        },
        xAxis: {
          type: 'category',
          data: months,
          axisLine: { lineStyle: { color: '#e5e7eb' } },
          axisLabel: { color: '#6b7280', fontSize: 11 }
        },
        yAxis: {
          type: 'value',
          axisLine: { show: false },
          splitLine: { lineStyle: { color: '#f3f4f6', type: 'dashed' } },
          axisLabel: {
            color: '#6b7280',
            fontSize: 11,
            formatter: value => this.formatCurrency(value, this.currencyCode)
          }
        },
        series: [
          {
            name: String(this.selectedPreviousYear),
            type: 'line',
            data: previousYear,
            smooth: true,
            lineStyle: { width: 2.5, color: '#d1d5db' },
            itemStyle: { color: '#d1d5db' },
            areaStyle: {
              color: {
                type: 'linear',
                x: 0, y: 0, x2: 0, y2: 1,
                colorStops: [
                  { offset: 0, color: 'rgba(209, 213, 219, 0.15)' },
                  { offset: 1, color: 'rgba(209, 213, 219, 0)' }
                ]
              }
            }
          },
          {
            name: String(this.selectedCurrentYear),
            type: 'line',
            data: currentYear,
            smooth: true,
            lineStyle: { width: 2.5, color: '#374151' },
            itemStyle: { color: '#374151' },
            areaStyle: {
              color: {
                type: 'linear',
                x: 0, y: 0, x2: 0, y2: 1,
                colorStops: [
                  { offset: 0, color: 'rgba(55, 65, 81, 0.1)' },
                  { offset: 1, color: 'rgba(55, 65, 81, 0)' }
                ]
              }
            }
          }
        ]
      }
    }
  },
  async mounted() {
    await this.fetchMonthlySpendings()
  }
}
</script>
