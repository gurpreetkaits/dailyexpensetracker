<template>
  <div class="daily-bar-chart w-full max-w-full overflow-hidden">
    <!-- Date Filter Header -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2">
        <button
          @click="showDateFilter = !showDateFilter"
          class="flex items-center gap-1.5 px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <span>{{ filterLabel }}</span>
        </button>
        <button
          v-if="hasActiveFilter"
          @click="clearFilter"
          class="p-1.5 text-gray-400 hover:text-red-500 transition-colors"
          title="Clear filter"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div v-if="meta?.has_more" class="text-xs text-gray-400">
        Scroll left for older data
      </div>
    </div>

    <!-- Date Filter Panel -->
    <Transition name="slide">
      <div v-if="showDateFilter" class="mb-4 p-3 bg-gray-50 rounded-lg">
        <div class="flex flex-wrap items-end gap-3">
          <div class="flex-1 min-w-[140px]">
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input
              type="date"
              v-model="filterStartDate"
              :max="filterEndDate || today"
              class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div class="flex-1 min-w-[140px]">
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input
              type="date"
              v-model="filterEndDate"
              :min="filterStartDate"
              :max="today"
              class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <button
            @click="applyDateFilter"
            :disabled="!filterStartDate || !filterEndDate"
            class="px-4 py-1.5 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            Apply
          </button>
        </div>
        <!-- Quick filters -->
        <div class="flex flex-wrap gap-2 mt-3">
          <button
            v-for="quick in quickFilters"
            :key="quick.label"
            @click="applyQuickFilter(quick)"
            class="px-2 py-1 text-xs bg-white border border-gray-200 rounded hover:bg-gray-100 transition-colors"
          >
            {{ quick.label }}
          </button>
        </div>
      </div>
    </Transition>

    <!-- Y-Axis Labels -->
    <div class="flex w-full">
      <div class="flex flex-col justify-between h-[180px] pr-2 text-right min-w-[45px]">
        <div class="text-[10px] text-gray-400 flex items-center justify-end">
          <span>{{ formatAxisValue(maxValue) }}</span>
          <span class="ml-1 text-gray-300">-</span>
        </div>
        <div class="text-[10px] text-gray-400 flex items-center justify-end">
          <span>{{ formatAxisValue(maxValue * 0.75) }}</span>
          <span class="ml-1 text-gray-300">-</span>
        </div>
        <div class="text-[10px] text-gray-400 flex items-center justify-end">
          <span>{{ formatAxisValue(maxValue * 0.5) }}</span>
          <span class="ml-1 text-gray-300">-</span>
        </div>
        <div class="text-[10px] text-gray-400 flex items-center justify-end">
          <span>{{ formatAxisValue(maxValue * 0.25) }}</span>
          <span class="ml-1 text-gray-300">-</span>
        </div>
        <div class="text-[10px] text-gray-400 flex items-center justify-end">
          <span>0</span>
          <span class="ml-1 text-gray-300">-</span>
        </div>
      </div>

      <!-- Chart Area -->
      <div
        ref="scrollContainer"
        class="flex-1 overflow-x-auto overflow-y-hidden scrollbar-hide min-w-0"
        @scroll="handleScroll"
      >
        <!-- Loading indicator for infinite scroll -->
        <div v-if="isLoadingMore" class="absolute left-0 top-1/2 -translate-y-1/2 z-10">
          <div class="animate-spin h-5 w-5 border-2 border-blue-500 border-t-transparent rounded-full"></div>
        </div>

        <div class="flex gap-2 pr-4" style="width: max-content;">
          <div
            v-for="(day, index) in chartData"
            :key="day.date"
            class="flex flex-col items-center cursor-pointer group relative w-8"
            @click="selectDay(day)"
            @mouseenter="showTooltip(day, $event)"
            @mouseleave="hideTooltip"
          >
            <!-- Bars Container -->
            <div class="flex items-end justify-center gap-[2px] h-[160px] mb-2 w-full">
              <!-- Income Bar (Green) -->
              <div
                class="bar-animate w-[10px] rounded-t group-hover:scale-105 group-hover:brightness-110"
                :class="[
                  selectedDay?.date === day.date ? 'bg-green-500 scale-105' : 'bg-green-400',
                  { 'opacity-30': !day.income && !day.expense }
                ]"
                :style="{
                  height: getBarHeight(day.income) + 'px',
                  animationDelay: `${index * 15}ms`
                }"
              ></div>
              <!-- Expense Bar (Red) -->
              <div
                class="bar-animate w-[10px] rounded-t group-hover:scale-105 group-hover:brightness-110"
                :class="[
                  selectedDay?.date === day.date ? 'bg-red-500 scale-105' : 'bg-red-400',
                  { 'opacity-30': !day.income && !day.expense }
                ]"
                :style="{
                  height: getBarHeight(day.expense) + 'px',
                  animationDelay: `${index * 15 + 30}ms`
                }"
              ></div>
            </div>

            <!-- Day & Month Label -->
            <div
              class="text-center transition-colors leading-tight"
              :class="selectedDay?.date === day.date ? 'text-blue-600 font-semibold' : 'text-gray-500'"
            >
              <div class="text-[11px] font-medium">{{ day.dayNum }}</div>
              <div class="text-[9px] uppercase tracking-tight">{{ day.month }}</div>
            </div>

            <!-- Selection Indicator -->
            <div
              v-if="selectedDay?.date === day.date"
              class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-1.5 h-1.5 bg-blue-500 rounded-full"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tooltip -->
    <div
      v-if="tooltip.visible"
      class="fixed z-50 bg-gray-900 text-white text-xs rounded-lg px-3 py-2 shadow-lg pointer-events-none"
      :style="{ left: `${tooltip.x}px`, top: `${tooltip.y}px`, transform: 'translate(-50%, -100%)' }"
    >
      <div class="font-medium mb-1">{{ tooltip.day?.dayNum }} {{ tooltip.day?.month }}</div>
      <div class="flex items-center gap-3">
        <span class="flex items-center gap-1">
          <span class="w-2 h-2 bg-green-400 rounded-full"></span>
          {{ formatCurrency(tooltip.day?.income || 0) }}
        </span>
        <span class="flex items-center gap-1">
          <span class="w-2 h-2 bg-red-400 rounded-full"></span>
          {{ formatCurrency(tooltip.day?.expense || 0) }}
        </span>
      </div>
      <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-full">
        <div class="border-6 border-transparent border-t-gray-900"></div>
      </div>
    </div>

    <!-- Selected Day Summary -->
    <div v-if="selectedDay" class="mt-4 bg-gray-50 rounded-lg p-4">
      <div class="text-sm font-medium text-gray-700 mb-3">
        {{ formatDateLabel(selectedDay) }}
      </div>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="w-3 h-3 bg-green-500 rounded"></div>
          <div>
            <div class="text-xs text-gray-500">Income</div>
            <div class="text-base font-semibold text-green-600">{{ formatCurrency(selectedDay.income) }}</div>
          </div>
        </div>
        <div class="flex items-center gap-2 text-right">
          <div>
            <div class="text-xs text-gray-500">Expense</div>
            <div class="text-base font-semibold text-red-600">{{ formatCurrency(selectedDay.expense) }}</div>
          </div>
          <div class="w-3 h-3 bg-red-500 rounded"></div>
        </div>
      </div>
    </div>

    <!-- Legend -->
    <div class="flex items-center justify-center gap-6 mt-3 text-xs text-gray-500">
      <div class="flex items-center gap-1.5">
        <div class="w-3 h-3 bg-green-400 rounded"></div>
        <span>Income</span>
      </div>
      <div class="flex items-center gap-1.5">
        <div class="w-3 h-3 bg-red-400 rounded"></div>
        <span>Expense</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'

const props = defineProps({
  chartData: {
    type: Array,
    default: () => []
  },
  currencyCode: {
    type: String,
    default: 'USD'
  },
  selectedDay: {
    type: Object,
    default: null
  },
  meta: {
    type: Object,
    default: null
  },
  isLoadingMore: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['day-select', 'load-more', 'filter-change', 'clear-filter'])

const scrollContainer = ref(null)
const showDateFilter = ref(false)
const filterStartDate = ref('')
const filterEndDate = ref('')
const tooltip = ref({
  visible: false,
  x: 0,
  y: 0,
  day: null
})

const today = computed(() => new Date().toISOString().split('T')[0])

const hasActiveFilter = computed(() => {
  return props.meta?.start_date && props.meta?.end_date
})

const filterLabel = computed(() => {
  if (props.meta?.start_date && props.meta?.end_date) {
    const start = new Date(props.meta.start_date)
    const end = new Date(props.meta.end_date)
    return `${start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`
  }
  return 'Filter by Date'
})

const quickFilters = [
  { label: 'Last 7 days', days: 7 },
  { label: 'Last 30 days', days: 30 },
  { label: 'Last 90 days', days: 90 },
  { label: 'This Month', type: 'thisMonth' },
  { label: 'Last Month', type: 'lastMonth' },
  { label: 'This Year', type: 'thisYear' },
]

const maxValue = computed(() => {
  if (!props.chartData.length) return 1000
  const max = Math.max(
    ...props.chartData.map(d => Math.max(d.income || 0, d.expense || 0))
  )
  return max > 0 ? max * 1.1 : 1000
})

const getBarHeight = (value) => {
  if (!value || value === 0) return 2
  const maxHeight = 150
  const height = (value / maxValue.value) * maxHeight
  return Math.max(Math.round(height), 4)
}

const formatAxisValue = (value) => {
  if (value >= 1000000) {
    return (value / 1000000).toFixed(1) + 'M'
  } else if (value >= 1000) {
    return Math.round(value / 1000) + 'k'
  }
  return Math.round(value).toString()
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: props.currencyCode,
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0)
}

const formatDateLabel = (day) => {
  if (!day) return ''
  const date = new Date(day.date)
  return date.toLocaleDateString('en-US', {
    weekday: 'long',
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  })
}

const selectDay = (day) => {
  emit('day-select', day)
}

const showTooltip = (day, event) => {
  const rect = event.currentTarget.getBoundingClientRect()
  tooltip.value = {
    visible: true,
    x: rect.left + rect.width / 2,
    y: rect.top - 8,
    day
  }
}

const hideTooltip = () => {
  tooltip.value.visible = false
}

let scrollTimeout = null
const handleScroll = () => {
  hideTooltip()

  // Check if scrolled to the left edge for infinite scroll
  if (scrollContainer.value) {
    const { scrollLeft } = scrollContainer.value

    // Clear existing timeout
    if (scrollTimeout) clearTimeout(scrollTimeout)

    // Debounce the load more call
    scrollTimeout = setTimeout(() => {
      if (scrollLeft < 100 && props.meta?.has_more && !props.isLoadingMore) {
        emit('load-more')
      }
    }, 150)
  }
}

const applyDateFilter = () => {
  if (filterStartDate.value && filterEndDate.value) {
    emit('filter-change', {
      start_date: filterStartDate.value,
      end_date: filterEndDate.value
    })
    showDateFilter.value = false
  }
}

const applyQuickFilter = (filter) => {
  const now = new Date()
  let start, end

  if (filter.days) {
    end = new Date()
    start = new Date()
    start.setDate(start.getDate() - filter.days + 1)
  } else if (filter.type === 'thisMonth') {
    start = new Date(now.getFullYear(), now.getMonth(), 1)
    end = new Date()
  } else if (filter.type === 'lastMonth') {
    start = new Date(now.getFullYear(), now.getMonth() - 1, 1)
    end = new Date(now.getFullYear(), now.getMonth(), 0)
  } else if (filter.type === 'thisYear') {
    start = new Date(now.getFullYear(), 0, 1)
    end = new Date()
  }

  filterStartDate.value = start.toISOString().split('T')[0]
  filterEndDate.value = end.toISOString().split('T')[0]

  emit('filter-change', {
    start_date: filterStartDate.value,
    end_date: filterEndDate.value
  })
  showDateFilter.value = false
}

const clearFilter = () => {
  filterStartDate.value = ''
  filterEndDate.value = ''
  emit('clear-filter')
}

const scrollToEnd = () => {
  nextTick(() => {
    if (scrollContainer.value) {
      scrollContainer.value.scrollLeft = scrollContainer.value.scrollWidth
    }
  })
}

onMounted(() => {
  scrollToEnd()
})

watch(() => props.chartData, (newData, oldData) => {
  // Only scroll to end if it's a fresh load (not loading more)
  if (!oldData || oldData.length === 0 || newData.length <= oldData.length) {
    scrollToEnd()
  }
}, { deep: true })
</script>

<style scoped>
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.border-6 {
  border-width: 6px;
}

.bar-animate {
  animation: growUp 0.4s ease-out forwards;
  transform-origin: bottom center;
  transition: transform 0.2s ease, filter 0.2s ease, background-color 0.2s ease;
}

@keyframes growUp {
  0% {
    transform: scaleY(0);
    opacity: 0;
  }
  60% {
    transform: scaleY(1.05);
    opacity: 1;
  }
  100% {
    transform: scaleY(1);
    opacity: 1;
  }
}

.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
