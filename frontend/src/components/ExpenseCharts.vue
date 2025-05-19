<template>
  <div class="flex flex-col md:flex-row gap-4 w-full mb-4">
    <!-- Wallets Bar Chart Card -->
    <div class="flex-1 bg-white rounded-xl shadow-sm p-4 flex flex-col items-center justify-center min-h-[180px]">
      <h3 class="text-base font-semibold mb-2">Wallet Balances ({{ periodLabel }})</h3>
      <div v-if="!walletStore.wallets.length" class="w-full h-32 flex items-center justify-center text-gray-400">
        No wallet data available
      </div>
      <v-chart v-else class="w-full h-32" :option="walletChartOption" autoresize />
    </div>
    <!-- Income/Expense Bar Chart Card -->
    <div class="flex-1 bg-white rounded-xl shadow-sm p-4 flex flex-col items-center justify-center min-h-[180px]">
      <h3 class="text-base font-semibold mb-2">Income/Expense ({{ periodLabel }})</h3>
      <div v-if="!hasTransactionData" class="w-full h-32 flex items-center justify-center text-gray-400">
        No transaction data available
      </div>
      <v-chart v-else class="w-full h-32" :option="incomeExpenseChartOption" autoresize />
    </div>
  </div>
</template>

<script>
import { computed, onMounted } from 'vue'
import { useTransactionStore } from '../store/transaction'
import { useWalletStore } from '../store/wallet'
import { useSettingsStore } from '../store/settings'

export default {
  name: 'ExpenseCharts',
  props: {
    periodLabel: {
      type: String,
      required: true
    }
  },
  setup() {
    const transactionStore = useTransactionStore()
    const walletStore = useWalletStore()
    const settingsStore = useSettingsStore()

    const hasTransactionData = computed(() => {
      return transactionStore.getFilteredIncome > 0 || transactionStore.getFilteredExpenses > 0
    })

    const walletChartOption = computed(() => {
      if (!walletStore.wallets.length) return {}

      return {
        tooltip: {
          trigger: 'axis',
          formatter: (params) => {
            const param = params[0]
            return `${param.name}: ${settingsStore.currencySymbol}${param.value.toLocaleString()}`
          }
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true
        },
        xAxis: {
          type: 'category',
          data: walletStore.wallets.map(wallet => wallet.name),
          axisLabel: {
            interval: 0,
            rotate: 30,
            fontSize: 10
          }
        },
        yAxis: {
          type: 'value',
          axisLabel: {
            formatter: (value) => `${settingsStore.currencySymbol}${value.toLocaleString()}`
          }
        },
        series: [{
          name: 'Balance',
          type: 'bar',
          data: walletStore.wallets.map(wallet => wallet.balance),
          itemStyle: {
            color: (params) => {
              const wallet = walletStore.wallets[params.dataIndex]
              switch (wallet.type) {
                case 'bank': return '#10b981'
                case 'card': return '#3b82f6'
                case 'cash': return '#8b5cf6'
                default: return '#6b7280'
              }
            }
          }
        }]
      }
    })

    const incomeExpenseChartOption = computed(() => {
      if (!hasTransactionData.value) return {}

      const income = transactionStore.getFilteredIncome || 0
      const expense = transactionStore.getFilteredExpenses || 0

      return {
        tooltip: {
          trigger: 'axis',
          formatter: (params) => {
            return params.map(param => {
              const value = param.value
              const color = param.seriesName === 'Income' ? '#10b981' : '#ef4444'
              return `<span style="color:${color}">${param.seriesName}: ${settingsStore.currencySymbol}${value.toLocaleString()}</span>`
            }).join('<br/>')
          }
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true
        },
        xAxis: {
          type: 'category',
          data: ['Income', 'Expense'],
          axisLabel: {
            fontSize: 10
          }
        },
        yAxis: {
          type: 'value',
          axisLabel: {
            formatter: (value) => `${settingsStore.currencySymbol}${value.toLocaleString()}`
          }
        },
        series: [{
          name: 'Amount',
          type: 'bar',
          data: [
            {
              value: income,
              itemStyle: { color: '#10b981' }
            },
            {
              value: expense,
              itemStyle: { color: '#ef4444' }
            }
          ]
        }]
      }
    })

    onMounted(async () => {
      if (!walletStore.wallets.length) {
        await walletStore.fetchWallets()
      }
    })

    return {
      walletChartOption,
      incomeExpenseChartOption,
      hasTransactionData,
      walletStore
    }
  }
}
</script>

<style scoped>
.v-chart {
  width: 100%;
  height: 100%;
}
</style> 