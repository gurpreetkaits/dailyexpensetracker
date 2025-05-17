<template>
  <div class="bg-white rounded-xl shadow-sm p-4 h-full">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-xl font-semibold text-red-500">{{ formatCurrency(totalSpent, currencyCode) }}</h2>
        <p class="text-sm text-gray-500">Total Spent</p>
      </div>
      <div class="h-10 w-10 bg-red-100 rounded-full flex items-center justify-center">
        <ShoppingCart class="h-5 w-5 text-red-500" />
      </div>
    </div>
    <div class="flex gap-2 overflow-x-auto mb-4">
      <button v-for="filter in quickFilters" :key="filter.value" @click="$emit('filter-change', filter.value)"
        class="px-3 py-1.5 rounded-full text-xs whitespace-nowrap transition-all" 
        :class="selectedFilter === filter.value ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600'">
        {{ filter.label }}
      </button>
    </div>
    <div v-if="!loading" class="h-[400px]">
      <v-chart :option="chartOption" autoresize @click="handleBarClick" />
    </div>
    <div v-else class="h-[400px] flex items-center justify-center">
      <div class="text-gray-400">Loading...</div>
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
      const sortedCategories = [...this.categories].sort((a, b) => b.amount - a.amount)
      const categoryNames = sortedCategories.map(cat => this.capitalizeFirstLetter(cat.name))
      const amounts = sortedCategories.map(cat => cat.amount)
      const colors = sortedCategories.map(cat => cat.color  || '#2563eb')

      return {
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'shadow'
          },
          formatter: (params) => {
            const data = params[0]
            return `${data.name}: ${this.formatCurrency(data.value, this.currencyCode)}`
          }
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '15%',
          containLabel: true
        },
        xAxis: {
          type: 'category',
          data: categoryNames,
          axisLabel: {
            interval: 0,
            rotate: 45,
            align: 'right',
            fontSize: 11
          }
        },
        yAxis: {
          type: 'value',
          axisLabel: {
            formatter: (value) => this.formatCurrency(value, this.currencyCode)
          }
        },
        series: [
          {
            name: 'Spending',
            type: 'bar',
            data: amounts.map((amount, index) => ({
              value: amount,
              itemStyle: {
                color: colors[index]
              }
            })),
            label: {
              show: true,
              position: 'top',
              rotate: 45,
              color: '#222',
              fontSize: 11,
              align: 'left',
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
