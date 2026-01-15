<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-full">
    <!-- Header -->
    <div class="p-4">
      <div class="flex items-center justify-between mb-3">
        <div>
          <p class="text-gray-400 text-[10px] font-medium mb-1">Total Spent</p>
          <h2 class="text-xl font-semibold text-gray-900">{{ formatCurrency(totalSpent, currencyCode) }}</h2>
        </div>
        <div class="h-9 w-9 bg-red-50 rounded-lg flex items-center justify-center">
          <ShoppingCart class="h-4 w-4 text-red-400" />
        </div>
      </div>

      <!-- Filter Pills -->
      <div class="flex gap-1.5 overflow-x-auto">
        <button v-for="filter in quickFilters" :key="filter.value" @click="$emit('filter-change', filter.value)"
          class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-all"
          :class="selectedFilter === filter.value
            ? 'bg-blue-50 text-blue-600'
            : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
          {{ filter.label }}
        </button>
      </div>
    </div>

    <div class="p-4 pt-0">
      <div v-if="loading" class="h-[350px] flex items-center justify-center">
        <div class="flex flex-col items-center gap-2">
          <div class="h-8 w-8 border-2 border-gray-300 border-t-transparent rounded-full animate-spin"></div>
          <p class="text-gray-400 text-sm">Loading...</p>
        </div>
      </div>
      <div v-else-if="categories && categories.length > 0" class="h-[350px]">
        <v-chart :option="chartOption" autoresize @click="handleBarClick" style="height: 100%; width: 100%;" />
      </div>
      <div v-else class="h-[350px] flex items-center justify-center">
        <div class="text-center">
          <ShoppingCart class="h-10 w-10 text-gray-200 mx-auto mb-2" />
          <p class="text-gray-400 text-sm">No expenses yet</p>
        </div>
      </div>
    </div>

    <!-- Category Transactions Modal (Desktop) -->
    <div v-if="showCategoryModal && !isMobile" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
      <div class="bg-white rounded-xl shadow max-w-md w-full p-0 relative">
        <button @click="closeCategoryModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
        <h3 class="text-base font-semibold px-4 pt-4 pb-2">Transactions for {{ selectedCategory?.name }}</h3>
        <div v-if="loadingCategoryTx" class="text-center py-8 text-gray-400">Loading...</div>
        <div v-else-if="categoryTxError" class="text-center py-8 text-red-400">{{ categoryTxError }}</div>
        <div v-else-if="categoryTransactions.length === 0" class="text-gray-500 text-center py-8">
          No transactions found for this category.
        </div>
        <div v-else class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
          <div v-for="tx in categoryTransactions" :key="tx.id" class="py-2 px-4 flex items-center gap-2 text-xs">
            <div class="w-7 h-7 rounded-full flex items-center justify-center" :style="{ backgroundColor: selectedCategory.color + '15', color: selectedCategory.color }">
              <!-- <span class="text-lg">{{ selectedCategory.icon }}</span> -->
               <component :is="selectedCategory.icon" class="text-sm w-3 h-3" />
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-medium text-gray-900 truncate">{{ tx.note || 'No note' }}</div>
              <div class="text-xs text-gray-400 truncate">{{ formatDate(tx.transaction_date) }}</div>
            </div>
            <div class="text-right">
              <span class="font-medium text-red-600">-{{ formatCurrency(tx.amount, currencyCode) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Category Transactions BottomSheet (Mobile) -->
    <BottomSheet v-if="showCategoryModal && isMobile" v-model="showCategoryModal">
      <div class="p-2">
        <div class="flex justify-between items-center mb-2">
          <h3 class="text-base font-semibold">Transactions for {{ selectedCategory?.name }}</h3>
          <button @click="closeCategoryModal" class="text-gray-400 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        <div v-if="loadingCategoryTx" class="text-center py-8 text-gray-400">Loading...</div>
        <div v-else-if="categoryTxError" class="text-center py-8 text-red-400">{{ categoryTxError }}</div>
        <div v-else-if="categoryTransactions.length === 0" class="text-gray-500 text-center py-8">
          No transactions found for this category.
        </div>
        <div v-else class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
          <div v-for="tx in categoryTransactions" :key="tx.id" class="py-2 flex items-center gap-2 text-xs">
            <div class="w-7 h-7 rounded-full flex items-center justify-center" :style="{ backgroundColor: selectedCategory.color + '15', color: selectedCategory.color }">
              <span class="text-lg">{{ selectedCategory.icon }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-medium text-gray-900 truncate">{{ tx.note || 'No note' }}</div>
              <div class="text-xs text-gray-400 truncate">{{ formatDate(tx.transaction_date) }}</div>
            </div>
            <div class="text-right">
              <span class="font-medium text-red-600">-{{ formatCurrency(tx.amount, currencyCode) }}</span>
            </div>
          </div>
        </div>
      </div>
    </BottomSheet>
  </div>
</template>

<script>
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { BarChart } from 'echarts/charts'
import { GridComponent, TooltipComponent, LegendComponent } from 'echarts/components'
import VChart from 'vue-echarts'
import { ShoppingCart } from 'lucide-vue-next'
import { numberMixin } from '../../mixins/numberMixin'
import { mapState } from 'pinia'
import { useTransactionStore } from '../../store/transaction'
import BottomSheet from '../BottomSheet.vue'
import axios from 'axios'
import { getCategoryTransactions } from '../../services/TransactionService'
import { iconMixin } from '../../mixins/iconMixin'

use([CanvasRenderer, BarChart, GridComponent, TooltipComponent, LegendComponent])

export default {
  name: 'CategorySpendingsByChart',
  components: {
    VChart,
    ShoppingCart,
    BottomSheet
  },
  mixins: [numberMixin,iconMixin],
  props: {
    categories: {
      type: Array,
      required: true
    },
    loading: {
      type: Boolean,
      default: false
    },
    currencyCode: {
      type: String,
      required: true
    },
    selectedFilter: {
      type: String,
      default: 'month'
    }
  },
  data() {
    return {
      quickFilters: [
        { label: 'Weekly', value: 'week' },
        { label: 'Monthly', value: 'month' },
        { label: 'Yearly', value: 'year' },
        { label: 'Custom', value: 'custom' }
      ],
      showCategoryModal: false,
      selectedCategory: null,
      categoryTransactions: [],
      loadingCategoryTx: false,
      categoryTxError: null
    }
  },
  computed: {
    ...mapState(useTransactionStore, ['userStats']),
    totalSpent() {
      return this.userStats?.overview?.total_spent || 0
    },
    chartOption() {
      if (!this.categories || this.categories.length === 0) {
        return {}
      }
      const sortedCategories = [...this.categories].sort((a, b) => b.amount - a.amount)
      const categoryNames = sortedCategories.map(cat => this.capitalizeFirstLetter(cat.name || ''))
      const amounts = sortedCategories.map(cat => cat.amount || 0)
      const colors = sortedCategories.map(cat => this.lightenColor(cat.color || '#2563eb', 0.3))

      return {
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'shadow'
          },
          backgroundColor: 'rgba(255, 255, 255, 0.95)',
          borderColor: '#e5e7eb',
          borderWidth: 1,
          textStyle: { color: '#374151' },
          formatter: (params) => {
            const data = params[0]
            return `<div style="font-weight:500">${data.name}</div><div style="font-size:14px;font-weight:600">${this.formatCurrency(data.value, this.currencyCode)}</div>`
          }
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '15%',
          top: '8%',
          containLabel: true
        },
        xAxis: {
          type: 'category',
          data: categoryNames,
          axisLine: { lineStyle: { color: '#e5e7eb' } },
          axisLabel: {
            interval: 0,
            rotate: 45,
            align: 'right',
            fontSize: 10,
            color: '#6b7280'
          }
        },
        yAxis: {
          type: 'value',
          axisLine: { show: false },
          splitLine: { lineStyle: { color: '#f3f4f6', type: 'dashed' } },
          axisLabel: {
            fontSize: 10,
            color: '#9ca3af',
            formatter: (value) => this.formatCurrency(value, this.currencyCode)
          }
        },
        series: [
          {
            name: 'Spending',
            type: 'bar',
            barWidth: '60%',
            itemStyle: {
              borderRadius: [4, 4, 0, 0]
            },
            data: amounts.map((amount, index) => ({
              value: amount,
              itemStyle: {
                color: colors[index]
              }
            })),
            label: {
              show: true,
              position: 'top',
              color: '#6b7280',
              fontSize: 9,
              formatter: (params) => this.formatCurrency(params.value, this.currencyCode)
            }
          }
        ]
      }
    },
    isMobile() {
      return window.innerWidth < 640
    }
  },
  methods: {
    lightenColor(color, percent) {
      if (!color) return '#93c5fd'
      // Handle hex colors
      if (color.startsWith('#')) {
        const hex = color.replace('#', '')
        const num = parseInt(hex, 16)
        if (isNaN(num)) return color
        const r = Math.min(255, Math.floor((num >> 16) + (255 - (num >> 16)) * percent))
        const g = Math.min(255, Math.floor(((num >> 8) & 0x00FF) + (255 - ((num >> 8) & 0x00FF)) * percent))
        const b = Math.min(255, Math.floor((num & 0x0000FF) + (255 - (num & 0x0000FF)) * percent))
        return `rgb(${r}, ${g}, ${b})`
      }
      // Return original if not hex
      return color
    },
    capitalizeFirstLetter(string) {
      return string.charAt(0).toUpperCase() + string.slice(1)
    },
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('en-IN', { year: 'numeric', month: 'short', day: 'numeric' })
    },
    async handleBarClick(params) {
      const sortedCategories = [...this.categories].sort((a, b) => b.amount - a.amount)
      const category = sortedCategories[params.dataIndex]
      this.selectedCategory = category
      this.showCategoryModal = true
      this.loadingCategoryTx = true
      this.categoryTxError = null
      try {
        const filters = {
          category: category.id,
          ...this.$parent.getFilters // assumes parent passes getFilters or you can pass as prop
        }
        const response = await getCategoryTransactions(filters)
        this.categoryTransactions = response.transactions
      } catch (error) {
        this.categoryTxError = error.response?.data?.error || 'Failed to load transactions'
        this.categoryTransactions = []
      } finally {
        this.loadingCategoryTx = false
      }
    },
    closeCategoryModal() {
      this.showCategoryModal = false
      this.selectedCategory = null
      this.categoryTransactions = []
      this.categoryTxError = null
    }
  }
}
</script>
