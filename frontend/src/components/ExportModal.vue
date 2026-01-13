<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/50" @click="close"></div>

      <!-- Modal -->
      <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b">
          <h3 class="text-lg font-semibold text-gray-900">Export Transactions</h3>
          <button @click="close" class="p-1 text-gray-400 hover:text-gray-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="p-4 space-y-4">
          <!-- Format Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="fmt in formats"
                :key="fmt.value"
                @click="selectedFormat = fmt.value"
                class="flex flex-col items-center gap-1 p-3 rounded-lg border-2 transition-all"
                :class="selectedFormat === fmt.value
                  ? 'border-blue-500 bg-blue-50 text-blue-700'
                  : 'border-gray-200 hover:border-gray-300 text-gray-600'"
              >
                <component :is="fmt.icon" class="h-6 w-6" />
                <span class="text-xs font-medium">{{ fmt.label }}</span>
              </button>
            </div>
          </div>

          <!-- Date Range -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range (Optional)</label>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs text-gray-500 mb-1">From</label>
                <input
                  type="date"
                  v-model="startDate"
                  :max="endDate || today"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
              <div>
                <label class="block text-xs text-gray-500 mb-1">To</label>
                <input
                  type="date"
                  v-model="endDate"
                  :min="startDate"
                  :max="today"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Transaction Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type</label>
            <div class="flex gap-2">
              <button
                v-for="t in types"
                :key="t.value"
                @click="selectedType = t.value"
                class="flex-1 px-3 py-2 text-sm rounded-lg border transition-all"
                :class="selectedType === t.value
                  ? 'border-blue-500 bg-blue-50 text-blue-700 font-medium'
                  : 'border-gray-200 hover:border-gray-300 text-gray-600'"
              >
                {{ t.label }}
              </button>
            </div>
          </div>

          <!-- Quick Date Filters -->
          <div>
            <label class="block text-xs text-gray-500 mb-2">Quick Select</label>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="quick in quickFilters"
                :key="quick.label"
                @click="applyQuickFilter(quick)"
                class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded transition-colors"
              >
                {{ quick.label }}
              </button>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 p-4 border-t bg-gray-50">
          <button
            @click="close"
            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="handleExport"
            :disabled="exporting"
            class="flex items-center gap-2 px-4 py-2 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <svg v-if="exporting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            {{ exporting ? 'Exporting...' : 'Export' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, h } from 'vue'
import { exportTransactions } from '../services/TransactionService'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'exported'])

const selectedFormat = ref('xlsx')
const selectedType = ref('all')
const startDate = ref('')
const endDate = ref('')
const exporting = ref(false)

const today = computed(() => new Date().toISOString().split('T')[0])

// Icon components as render functions
const ExcelIcon = {
  render() {
    return h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5' })
    ])
  }
}

const CsvIcon = {
  render() {
    return h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z' })
    ])
  }
}

const PdfIcon = {
  render() {
    return h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '1.5', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z' })
    ])
  }
}

const formats = [
  { value: 'xlsx', label: 'Excel', icon: ExcelIcon },
  { value: 'csv', label: 'CSV', icon: CsvIcon },
  { value: 'pdf', label: 'PDF', icon: PdfIcon },
]

const types = [
  { value: 'all', label: 'All' },
  { value: 'income', label: 'Income' },
  { value: 'expense', label: 'Expense' },
]

const quickFilters = [
  { label: 'This Month', type: 'thisMonth' },
  { label: 'Last Month', type: 'lastMonth' },
  { label: 'Last 3 Months', type: 'last3Months' },
  { label: 'This Year', type: 'thisYear' },
  { label: 'All Time', type: 'allTime' },
]

const applyQuickFilter = (filter) => {
  const now = new Date()

  if (filter.type === 'thisMonth') {
    startDate.value = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0]
    endDate.value = today.value
  } else if (filter.type === 'lastMonth') {
    startDate.value = new Date(now.getFullYear(), now.getMonth() - 1, 1).toISOString().split('T')[0]
    endDate.value = new Date(now.getFullYear(), now.getMonth(), 0).toISOString().split('T')[0]
  } else if (filter.type === 'last3Months') {
    startDate.value = new Date(now.getFullYear(), now.getMonth() - 2, 1).toISOString().split('T')[0]
    endDate.value = today.value
  } else if (filter.type === 'thisYear') {
    startDate.value = new Date(now.getFullYear(), 0, 1).toISOString().split('T')[0]
    endDate.value = today.value
  } else if (filter.type === 'allTime') {
    startDate.value = ''
    endDate.value = ''
  }
}

const close = () => {
  emit('close')
}

const handleExport = async () => {
  exporting.value = true
  try {
    await exportTransactions({
      format: selectedFormat.value,
      start_date: startDate.value || undefined,
      end_date: endDate.value || undefined,
      type: selectedType.value,
    })
    emit('exported')
    close()
  } catch (error) {
    console.error('Export failed:', error)
    alert('Export failed. Please try again.')
  } finally {
    exporting.value = false
  }
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
}
</style>
