<template>
  <div class="bg-white rounded-lg shadow-sm p-4 h-full flex flex-col mb-6 md:mb-0">
      <h3 class="text-base font-semibold mb-2">Monthly Spendings Comparison</h3>
      <div class="flex gap-4 mb-4 items-center justify-center mt-10">
        <select v-model="selectedYear1" class="px-3 py-1.5 rounded-full text-xs whitespace-nowrap transition-all min-w-[80px]">
          <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
        </select>
        <span class="text-gray-400">vs</span>
        <select v-model="selectedYear2" class="px-3 py-1.5 rounded-full text-xs whitespace-nowrap transition-all min-w-[80px]">
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
      type: Function,
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
      selectedYear1: currentYear,
      selectedYear2: currentYear - 1,
      monthlySpendings: [],
      lastYearMonthlySpendings: [],
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
  watch: {
    filter: {
      immediate: true,
      handler() {
        this.fetchMonthlySpendings()
      }
    },
    getFilters: {
      deep: true,
      handler() {
        this.fetchMonthlySpendings()
      }
    },
    selectedYear1() {
      this.fetchMonthlySpendings()
    },
    selectedYear2() {
      this.fetchMonthlySpendings()
    }
  },
  methods: {
    async fetchMonthlySpendings() {
      // Dummy data for testing
      // In real use, fetch for selectedYear1 and selectedYear2
      if (this.selectedYear1 === this.selectedYear2) {
        this.option = { ...this.option, series: [], legend: { data: [] } }
        return
      }
      const dummyData = {
        [new Date().getFullYear()]: [
          { label: 'Jan', amount: 1200 }, { label: 'Feb', amount: 900 }, { label: 'Mar', amount: 1500 }, { label: 'Apr', amount: 800 },
          { label: 'May', amount: 1100 }, { label: 'Jun', amount: 950 }, { label: 'Jul', amount: 1300 }, { label: 'Aug', amount: 1000 },
          { label: 'Sep', amount: 1400 }, { label: 'Oct', amount: 1200 }, { label: 'Nov', amount: 1600 }, { label: 'Dec', amount: 1700 }
        ],
        [new Date().getFullYear() - 1]: [
          { label: 'Jan', amount: 1000 }, { label: 'Feb', amount: 1100 }, { label: 'Mar', amount: 1200 }, { label: 'Apr', amount: 900 },
          { label: 'May', amount: 1000 }, { label: 'Jun', amount: 1050 }, { label: 'Jul', amount: 1200 }, { label: 'Aug', amount: 950 },
          { label: 'Sep', amount: 1100 }, { label: 'Oct', amount: 1150 }, { label: 'Nov', amount: 1300 }, { label: 'Dec', amount: 1400 }
        ],
        [new Date().getFullYear() - 2]: [
          { label: 'Jan', amount: 800 }, { label: 'Feb', amount: 850 }, { label: 'Mar', amount: 900 }, { label: 'Apr', amount: 950 },
          { label: 'May', amount: 1000 }, { label: 'Jun', amount: 1050 }, { label: 'Jul', amount: 1100 }, { label: 'Aug', amount: 1150 },
          { label: 'Sep', amount: 1200 }, { label: 'Oct', amount: 1250 }, { label: 'Nov', amount: 1300 }, { label: 'Dec', amount: 1350 }
        ],
        [new Date().getFullYear() - 3]: [
          { label: 'Jan', amount: 700 }, { label: 'Feb', amount: 750 }, { label: 'Mar', amount: 800 }, { label: 'Apr', amount: 850 },
          { label: 'May', amount: 900 }, { label: 'Jun', amount: 950 }, { label: 'Jul', amount: 1000 }, { label: 'Aug', amount: 1050 },
          { label: 'Sep', amount: 1100 }, { label: 'Oct', amount: 1150 }, { label: 'Nov', amount: 1200 }, { label: 'Dec', amount: 1250 }
        ]
      }
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
      const year1Data = Array(12).fill(0)
      const year2Data = Array(12).fill(0)
      dummyData[this.selectedYear1].forEach(m => {
        const idx = months.indexOf(m.label)
        if (idx !== -1) year1Data[idx] = m.amount
      })
      dummyData[this.selectedYear2].forEach(m => {
        const idx = months.indexOf(m.label)
        if (idx !== -1) year2Data[idx] = m.amount
      })
      this.option = {
        title: { text: '' },
        tooltip: { trigger: 'axis' },
        legend: { data: [String(this.selectedYear1), String(this.selectedYear2)] },
        xAxis: { type: 'category', data: months },
        yAxis: {
          type: 'value',
          axisLabel: {
            formatter: value => this.formatCurrency(value, this.currencyCode)
          }
        },
        series: [
          { name: String(this.selectedYear1), type: 'line', data: year1Data },
          { name: String(this.selectedYear2), type: 'line', data: year2Data }
        ]
      }
    }
  }
}
</script>
