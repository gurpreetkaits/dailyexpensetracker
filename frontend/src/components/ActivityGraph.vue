<template>
  <div class="activity-graph">
    <!-- Month labels -->
    <div class="flex mb-1">
      <div class="w-8"></div>
      <div class="flex-1 flex text-[10px] text-gray-400">
        <span v-for="month in monthLabels" :key="month.key"
              :style="{ marginLeft: month.offset + 'px', width: month.width + 'px' }">
          {{ month.label }}
        </span>
      </div>
    </div>

    <!-- Graph grid -->
    <div class="flex">
      <!-- Day labels -->
      <div class="flex flex-col justify-around text-[10px] text-gray-400 w-8 pr-1">
        <span>Mon</span>
        <span>Wed</span>
        <span>Fri</span>
      </div>

      <!-- Activity squares -->
      <div class="flex gap-[3px] overflow-x-auto scrollbar-hide">
        <div v-for="(week, weekIndex) in weeks" :key="weekIndex" class="flex flex-col gap-[3px]">
          <div
            v-for="(day, dayIndex) in week"
            :key="dayIndex"
            class="w-[11px] h-[11px] rounded-sm transition-all cursor-pointer"
            :class="getActivityClass(day)"
            :title="getTooltip(day)"
            @mouseenter="hoveredDay = day"
            @mouseleave="hoveredDay = null"
          ></div>
        </div>
      </div>
    </div>

    <!-- Legend -->
    <div class="flex items-center justify-end gap-2 mt-3 text-[10px] text-gray-400">
      <span>Less</span>
      <div class="flex gap-[2px]">
        <div class="w-[11px] h-[11px] rounded-sm bg-white border border-gray-200"></div>
        <div class="w-[11px] h-[11px] rounded-sm bg-emerald-200"></div>
        <div class="w-[11px] h-[11px] rounded-sm bg-emerald-400"></div>
        <div class="w-[11px] h-[11px] rounded-sm bg-emerald-500"></div>
        <div class="w-[11px] h-[11px] rounded-sm bg-emerald-600"></div>
      </div>
      <span>More</span>
    </div>

    <!-- Stats summary -->
    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
      <div class="text-sm text-gray-600">
        <span class="font-semibold text-gray-900">{{ totalActiveDays }}</span> days tracked in the last year
      </div>
      <div v-if="currentStreak > 0" class="flex items-center gap-1 text-sm">
        <Flame class="w-4 h-4 text-orange-500" />
        <span class="font-semibold text-orange-500">{{ currentStreak }}</span>
        <span class="text-gray-500">day streak</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Flame } from 'lucide-vue-next'

const props = defineProps({
  activityData: {
    type: Object,
    default: () => ({})
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const hoveredDay = ref(null)

// Calculate activity thresholds based on user's data
const activityThresholds = computed(() => {
  const counts = Object.values(props.activityData).filter(c => c > 0).sort((a, b) => a - b)

  if (counts.length === 0) {
    return { q1: 1, q2: 2, q3: 3, q4: 4 }
  }

  const max = Math.max(...counts)

  // If max is small, use simple thresholds
  if (max <= 4) {
    return { q1: 1, q2: 2, q3: 3, q4: 4 }
  }

  // Calculate quartiles for dynamic thresholds
  const q1Index = Math.floor(counts.length * 0.25)
  const q2Index = Math.floor(counts.length * 0.5)
  const q3Index = Math.floor(counts.length * 0.75)

  return {
    q1: counts[q1Index] || 1,      // 25th percentile - light
    q2: counts[q2Index] || 2,      // 50th percentile - medium
    q3: counts[q3Index] || 3,      // 75th percentile - dark
    q4: max                         // max - darkest
  }
})

const weeks = computed(() => {
  const result = []
  const today = new Date()
  const oneYearAgo = new Date(today)
  oneYearAgo.setFullYear(oneYearAgo.getFullYear() - 1)

  // Start from the beginning of the week (Sunday) one year ago
  const startDate = new Date(oneYearAgo)
  startDate.setDate(startDate.getDate() - startDate.getDay())

  let currentWeek = []
  let currentDate = new Date(startDate)

  while (currentDate <= today) {
    const dateStr = formatDate(currentDate)
    const count = props.activityData[dateStr] || 0
    const dayOfWeek = currentDate.getDay()

    // Start new week on Sunday
    if (dayOfWeek === 0 && currentWeek.length > 0) {
      result.push(currentWeek)
      currentWeek = []
    }

    currentWeek.push({
      date: new Date(currentDate),
      dateStr,
      count,
      dayOfWeek
    })

    currentDate.setDate(currentDate.getDate() + 1)
  }

  // Add the last week
  if (currentWeek.length > 0) {
    // Pad with empty days if needed
    while (currentWeek.length < 7) {
      currentWeek.push(null)
    }
    result.push(currentWeek)
  }

  return result
})

const monthLabels = computed(() => {
  const labels = []
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
  let currentMonth = -1

  weeks.value.forEach((week, index) => {
    const firstDay = week.find(d => d !== null)
    if (firstDay) {
      const month = firstDay.date.getMonth()
      if (month !== currentMonth) {
        labels.push({
          label: months[month],
          key: `${month}-${index}`,
          offset: index === 0 ? 0 : 0,
          width: 0,
          weekIndex: index
        })
        currentMonth = month
      }
    }
  })

  // Calculate widths based on week positions
  for (let i = 0; i < labels.length; i++) {
    const nextIndex = i < labels.length - 1 ? labels[i + 1].weekIndex : weeks.value.length
    labels[i].width = (nextIndex - labels[i].weekIndex) * 14 // 11px square + 3px gap
  }

  return labels
})

const totalActiveDays = computed(() => {
  return Object.values(props.activityData).filter(count => count > 0).length
})

const currentStreak = computed(() => {
  let streak = 0
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  for (let i = 0; i <= 365; i++) {
    const checkDate = new Date(today)
    checkDate.setDate(checkDate.getDate() - i)
    const dateStr = formatDate(checkDate)

    if (props.activityData[dateStr] > 0) {
      streak++
    } else if (i > 0) {
      // Allow for today to be empty (user might not have tracked yet today)
      break
    }
  }

  return streak
})

function formatDate(date) {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

function getActivityClass(day) {
  if (!day) return 'bg-transparent'

  const count = day.count
  const t = activityThresholds.value

  // No activity = white
  if (count === 0) return 'bg-white border border-gray-200'

  // Level 1: Light (1-2 or below 25th percentile)
  if (count <= Math.max(2, t.q1)) return 'bg-emerald-200'

  // Level 2: Medium (below 50th percentile)
  if (count <= t.q2) return 'bg-emerald-400'

  // Level 3: Dark (below 75th percentile)
  if (count <= t.q3) return 'bg-emerald-500'

  // Level 4: Darkest (above 75th percentile)
  return 'bg-emerald-600'
}

function getTooltip(day) {
  if (!day) return ''

  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
  const dateStr = day.date.toLocaleDateString('en-US', options)
  const count = day.count

  if (count === 0) return `No transactions on ${dateStr}`
  if (count === 1) return `1 transaction on ${dateStr}`
  return `${count} transactions on ${dateStr}`
}
</script>

<style scoped>
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
</style>
