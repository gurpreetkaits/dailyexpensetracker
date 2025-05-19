<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="transform translate-x-full"
    enter-to-class="transform translate-x-0"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="transform translate-x-0"
    leave-to-class="transform translate-x-full"
  >
    <div v-if="modelValue"
         class="fixed inset-y-0 right-0 w-full max-w-lg bg-white shadow-xl z-50 overflow-y-auto">
      <!-- Header -->
      <div class="sticky top-0 bg-white border-b z-10">
        <div class="flex items-center justify-between p-4">
          <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-full flex items-center justify-center shrink-0"
                 :class="{
                   'bg-emerald-100': wallet?.type === 'bank',
                   'bg-blue-100': wallet?.type === 'card',
                   'bg-purple-100': wallet?.type === 'cash'
                 }">
              <component :is="getWalletTypeIcon(wallet?.type)"
                        class="h-4 w-4"
                        :class="{
                          'text-emerald-600': wallet?.type === 'bank',
                          'text-blue-600': wallet?.type === 'card',
                          'text-purple-600': wallet?.type === 'cash'
                        }" />
            </div>
            <div>
              <h2 class="text-lg font-medium text-gray-900">{{ wallet?.name }}</h2>
              <p class="text-sm text-gray-500">{{ wallet?.type }} Wallet</p>
            </div>
          </div>
          <button @click="$emit('update:modelValue', false)"
                  class="p-1 text-gray-400 hover:text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="p-4 space-y-6">
        <!-- Balance Card -->
        <div class="relative overflow-hidden rounded-xl p-4"
             :class="{
               'bg-gradient-to-br from-emerald-500 to-emerald-600': wallet?.type === 'bank',
               'bg-gradient-to-br from-blue-500 to-blue-600': wallet?.type === 'card',
               'bg-gradient-to-br from-purple-500 to-purple-600': wallet?.type === 'cash'
             }">
          <!-- Decorative Elements -->
          <div class="absolute inset-0 opacity-10">
            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white"></div>
            <div class="absolute -left-4 -bottom-4 h-32 w-32 rounded-full bg-white"></div>
          </div>
          
          <div class="relative">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-sm text-white/80">Current Balance</p>
                <p class="text-2xl font-semibold text-white mt-1">
                  {{ formatCurrency(wallet?.balance) }}
                </p>
              </div>
              <span class="px-2 py-1 rounded-full text-xs bg-white/20 text-white backdrop-blur-sm">
                {{ wallet?.currency }}
              </span>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-white/80">Transactions</p>
                <p class="text-lg font-medium text-white mt-1">
                  {{ wallet?.transaction_count || 0 }}
                </p>
              </div>
              <div>
                <p class="text-sm text-white/80">Created</p>
                <p class="text-lg font-medium text-white mt-1">
                  {{ formatDate(wallet?.created_at) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Balance History -->
        <div>
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-medium text-gray-900">Balance History</h3>
            <div class="flex gap-2">
              <button
                v-for="period in periods"
                :key="period.value"
                @click="selectedPeriod = period.value"
                class="px-2 py-1 text-xs rounded-md transition-colors"
                :class="selectedPeriod === period.value
                  ? 'bg-blue-100 text-blue-700'
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
              >
                {{ period.label }}
              </button>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="flex items-center justify-center h-48">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
          </div>

          <!-- Custom Balance History Visualization -->
          <div v-else-if="balanceHistory.length > 0" class="space-y-4">
            <div class="h-48">
              <v-chart class="w-full h-full" :option="chartOption" autoresize />
            </div>

            <!-- Summary -->
            <div class="mt-4 grid grid-cols-3 gap-4">
              <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs text-gray-500">Starting</p>
                <p class="text-sm font-medium text-gray-900">{{ formatCurrency(startingBalance) }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs text-gray-500">Current</p>
                <p class="text-sm font-medium text-gray-900">{{ formatCurrency(currentBalance) }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs text-gray-500">Net Change</p>
                <p class="text-sm font-medium" :class="netChange >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ formatCurrency(netChange) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="flex flex-col items-center justify-center h-48 text-gray-500">
            <Wallet class="h-8 w-8 mb-2" />
            <p class="text-sm">No balance history available</p>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div>
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-medium text-gray-900">Recent Transactions</h3>
            <button @click="viewAllTransactions"
                    class="text-sm text-blue-600 hover:text-blue-700">
              View All
            </button>
          </div>

          <div v-if="transactions.length > 0" class="space-y-3">
            <div v-for="transaction in transactions"
                 :key="transaction.id"
                 class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-full flex items-center justify-center"
                     :class="{
                       'bg-emerald-100': transaction.type === 'income',
                       'bg-red-100': transaction.type === 'expense',
                       'bg-blue-100': transaction.type === 'transfer_in' || transaction.type === 'transfer_out'
                     }">
                  <component :is="getTransactionTypeIcon(transaction.type)"
                            class="h-4 w-4"
                            :class="{
                              'text-emerald-600': transaction.type === 'income',
                              'text-red-600': transaction.type === 'expense',
                              'text-blue-600': transaction.type === 'transfer_in' || transaction.type === 'transfer_out'
                            }" />
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ transaction.description }}</p>
                  <p class="text-xs text-gray-500">{{ formatDate(transaction.transaction_date) }}</p>
                </div>
              </div>
              <p class="text-sm font-medium"
                 :class="{
                   'text-emerald-600': transaction.type === 'income' || transaction.type === 'transfer_in',
                   'text-red-600': transaction.type === 'expense',
                   'text-blue-600': transaction.type === 'transfer_out'
                 }">
                {{ formatCurrency(Math.abs(transaction.amount)) }}
              </p>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="flex flex-col items-center justify-center h-32 text-gray-500">
            <Wallet class="h-6 w-6 mb-2" />
            <p class="text-sm">No transactions yet</p>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { Wallet, CreditCard, Banknote, Landmark, ArrowUpRight, ArrowDownLeft, ArrowLeftRight } from 'lucide-vue-next'
import { useWalletStore } from '../store/wallet'
import { useRouter } from 'vue-router'
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
  modelValue: {
    type: Boolean,
    required: true
  },
  wallet: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])
const router = useRouter()
const walletStore = useWalletStore()
const loading = ref(false)
const selectedPeriod = ref('month')
const transactions = ref([])

const periods = [
  { label: 'Week', value: 'week' },
  { label: 'Month', value: 'month' },
  { label: 'Year', value: 'year' }
]

const balanceHistory = computed(() => {
  const history = walletStore.balanceHistory[props.wallet?.id]?.data || []
  console.log('Balance history data:', history) // For debugging
  return history.map(record => ({
    balance: parseFloat(record.balance),
    created_at: record.created_at
  }))
})

const startingBalance = computed(() => balanceHistory.value[0]?.balance || 0)
const currentBalance = computed(() => balanceHistory.value[balanceHistory.value.length - 1]?.balance || 0)
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
    currency: props.wallet?.currency || 'USD'
  }).format(amount)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  })
}

const getWalletTypeIcon = (type) => {
  switch (type) {
    case 'bank':
      return Landmark
    case 'card':
      return CreditCard
    case 'cash':
      return Banknote
    default:
      return Wallet
  }
}

const getTransactionTypeIcon = (type) => {
  switch (type) {
    case 'income':
      return ArrowUpRight
    case 'expense':
      return ArrowDownLeft
    case 'transfer_in':
    case 'transfer_out':
      return ArrowLeftRight
    default:
      return Wallet
  }
}

const fetchData = async () => {
  if (!props.wallet?.id) return

  loading.value = true
  try {
    await Promise.all([
      walletStore.fetchWalletBalanceHistory(props.wallet.id, selectedPeriod.value),
      walletStore.fetchWalletTransactions(props.wallet.id, 1)
    ])
    transactions.value = walletStore.transactions.slice(0, 5)
  } catch (error) {
    console.error('Error fetching wallet data:', error)
  } finally {
    loading.value = false
  }
}

const viewAllTransactions = () => {
  router.push(`/wallets/${props.wallet.id}/transactions`)
  emit('update:modelValue', false)
}

watch([() => props.wallet?.id, selectedPeriod], () => {
  fetchData()
})

onMounted(() => {
  fetchData()
})
</script>
