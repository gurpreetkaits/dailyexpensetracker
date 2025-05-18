<template>
  <div class="bg-white rounded-lg shadow-sm p-4 h-full flex flex-col mb-6 md:mb-0">
      <h3 class="text-base font-semibold mb-2">Monthly Spendings Comparison</h3>
      <div class="flex gap-4 mb-4 items-center justify-center mt-10">
        <select v-model="selectedPreviousYear" class="px-3 py-1.5 rounded-full text-xs whitespace-nowrap transition-all min-w-[80px]">
          <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
        </select>
        <span class="text-gray-400">vs</span>
        <select v-model="selectedCurrentYear" class="px-3 py-1.5 rounded-full text-xs whitespace-nowrap transition-all min-w-[80px]">
          <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
        </select>
      </div>
      <div class="mt-">
          <v-chart :option="option" style="height: 350px;" />
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
        tooltip: { trigger: 'axis' },
        legend: { data: [String(this.selectedPreviousYear), String(this.selectedCurrentYear)] },
        xAxis: { type: 'category', data: months },
        yAxis: {
          type: 'value',
          axisLabel: {
            formatter: value => this.formatCurrency(value, this.currencyCode)
          }
        },
        series: [
          { name: String(this.selectedPreviousYear), type: 'line', data: previousYear },
          { name: String(this.selectedCurrentYear), type: 'line', data: currentYear }
        ]
      }
    }
  },
  async mounted() {
    await this.fetchMonthlySpendings()
  }
}
</script>
