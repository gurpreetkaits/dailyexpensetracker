<template>
  <div class="p-4">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-medium text-gray-900">Balance History</h3>
      <div class="flex gap-2">
        <button
          v-for="period in periods"
          :key="period.value"
          @click="selectedPeriod = period.value"
          class="px-2 py-1 text-sm rounded-md transition-colors"
          :class="selectedPeriod === period.value
            ? 'bg-blue-100 text-blue-700'
            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
        >
          {{ period.label }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center h-64">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <!-- Replace the custom visualization with ECharts -->
    <div v-else-if="balanceHistory.length > 0" class="space-y-4">
      <div class="h-64">
        <v-chart class="w-full h-full" :option="chartOption" autoresize />
      </div>

      <!-- Summary -->
      <div class="mt-4 grid grid-cols-3 gap-4">
        <div class="bg-gray-50 rounded-lg p-3">
          <p class="text-sm text-gray-500">Starting Balance</p>
          <p class="text-lg font-medium text-gray-900">{{ formatCurrency(startingBalance) }}</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-3">
          <p class="text-sm text-gray-500">Current Balance</p>
          <p class="text-lg font-medium text-gray-900">{{ formatCurrency(currentBalance) }}</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-3">
          <p class="text-sm text-gray-500">Net Change</p>
          <p class="text-lg font-medium" :class="netChange >= 0 ? 'text-green-600' : 'text-red-600'">
            {{ formatCurrency(netChange) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="flex flex-col items-center justify-center h-64 text-gray-500">
      <Wallet class="h-8 w-8 mb-2" />
      <p>No balance history available</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { Wallet } from 'lucide-vue-next'
import { useWalletStore } from '../store/wallet'
import { useSettingsStore } from '../store/settings'
import VChart from 'vue-echarts'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { LineChart } from 'echarts/charts'
import {
  GridComponent,
  TooltipComponent,
  ToolboxComponent,
  DataZoomComponent
} from 'echarts/components'

// Register ECharts components
use([
  CanvasRenderer,
  LineChart,
  GridComponent,
  TooltipComponent,
  ToolboxComponent,
  DataZoomComponent
])

const props = defineProps({
  walletId: {
    type: [String, Number],
    required: true
  }
})

const walletStore = useWalletStore()
const settingsStore = useSettingsStore()
const loading = ref(false)
const selectedPeriod = ref('month')

const periods = [
  { label: 'Week', value: 'week' },
  { label: 'Month', value: 'month' },
  { label: 'Year', value: 'year' }
]

const balanceHistory = computed(() => {
  const history = walletStore.balanceHistory[props.walletId]?.data || []
  // Convert the reactive array to a regular array and sort by created_at
  return [...history]
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    .map(record => ({
      balance: parseFloat(record.balance),
      created_at: record.created_at
    }))
})

const startingBalance = computed(() => {
  const history = walletStore.balanceHistory[props.walletId]?.data || []
  return history.length > 0 ? parseFloat(history[history.length - 1].balance) : 0
})

const currentBalance = computed(() => {
  const history = walletStore.balanceHistory[props.walletId]?.data || []
  return history.length > 0 ? parseFloat(history[0].balance) : 0
})

const netChange = computed(() => currentBalance.value - startingBalance.value)

const chartOption = computed(() => ({
  tooltip: {
    trigger: 'axis',
    formatter: (params) => {
      const data = params[0]
      return `${formatDate(data.value[0])}<br/>${formatCurrency(data.value[1])}`
    }
  },
  grid: {
    left: '3%',
    right: '4%',
    bottom: '3%',
    containLabel: true
  },
  xAxis: {
    type: 'time',
    axisLabel: {
      formatter: (value) => formatDate(value)
    }
  },
  yAxis: {
    type: 'value',
    axisLabel: {
      formatter: (value) => formatCurrency(value)
    }
  },
  dataZoom: [
    {
      type: 'inside',
      start: 0,
      end: 100
    },
    {
      start: 0,
      end: 100
    }
  ],
  series: [
    {
      name: 'Balance',
      type: 'line',
      smooth: true,
      data: balanceHistory.value.map(record => [record.created_at, record.balance]),
      itemStyle: {
        color: '#3B82F6'
      },
      areaStyle: {
        color: {
          type: 'linear',
          x: 0,
          y: 0,
          x2: 0,
          y2: 1,
          colorStops: [
            {
              offset: 0,
              color: 'rgba(59, 130, 246, 0.2)'
            },
            {
              offset: 1,
              color: 'rgba(59, 130, 246, 0)'
            }
          ]
        }
      }
    }
  ]
}))

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: settingsStore.currencyCode || 'USD',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  })
}

const fetchBalanceHistory = async () => {
  loading.value = true
  try {
    await walletStore.fetchWalletBalanceHistory(props.walletId, selectedPeriod.value)
  } catch (error) {
    console.error('Error fetching balance history:', error)
  } finally {
    loading.value = false
  }
}

watch([() => props.walletId, selectedPeriod], () => {
  fetchBalanceHistory()
})

onMounted(() => {
  fetchBalanceHistory()
})
</script>

<style scoped>
.group:hover .group-hover\:opacity-100 {
  z-index: 20;
}
</style>
