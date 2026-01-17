<template>
  <div class="max-w-7xl mx-auto relative">
    <div class="relative pb-24 px-3 pt-2">
      <!-- Profile Header Card -->
      <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
        <div class="flex items-center gap-4">
          <!-- Avatar -->
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white text-2xl font-semibold flex-shrink-0">
            {{ userInitials }}
          </div>

          <!-- User Info -->
          <div class="flex-1 min-w-0">
            <h2 class="text-lg font-semibold text-gray-900 truncate">{{ user?.name || 'User' }}</h2>
            <p class="text-sm text-gray-500 truncate">{{ user?.email }}</p>
            <p class="text-xs text-gray-400 mt-1">
              Member since {{ formatMemberSince(user?.created_at) }}
            </p>
          </div>

        </div>
      </div>

      <!-- Activity Graph Card -->
      <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-base font-semibold text-gray-900">Expense Tracking Activity</h3>
            <p class="text-xs text-gray-500 mt-0.5">Your transaction history over the past year</p>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500"></div>
        </div>

        <!-- Activity Graph -->
        <ActivityGraph v-else :activityData="activityData" />
      </div>

      <!-- Quick Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
              <Receipt class="w-4 h-4 text-blue-600" />
            </div>
          </div>
          <p class="text-xl font-semibold text-gray-900">{{ stats.totalTransactions }}</p>
          <p class="text-xs text-gray-500">Total Transactions</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
              <TrendingUp class="w-4 h-4 text-emerald-600" />
            </div>
          </div>
          <p class="text-xl font-semibold text-gray-900">{{ stats.activeDays }}</p>
          <p class="text-xs text-gray-500">Active Days</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
              <Flame class="w-4 h-4 text-orange-600" />
            </div>
          </div>
          <p class="text-xl font-semibold text-gray-900">{{ stats.longestStreak }}</p>
          <p class="text-xs text-gray-500">Longest Streak</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
              <Calendar class="w-4 h-4 text-purple-600" />
            </div>
          </div>
          <p class="text-xl font-semibold text-gray-900">{{ stats.avgPerDay }}</p>
          <p class="text-xs text-gray-500">Avg per Active Day</p>
        </div>
      </div>

      <!-- Account Links -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <router-link
          to="/settings/general"
          class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors border-b border-gray-100"
        >
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center">
              <Settings class="w-4 h-4 text-gray-600" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">General Settings</p>
              <p class="text-xs text-gray-500">Currency and preferences</p>
            </div>
          </div>
          <ChevronRight class="w-5 h-5 text-gray-400" />
        </router-link>

        <router-link
          to="/settings/subscription"
          class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
        >
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center">
              <Crown class="w-4 h-4 text-emerald-600" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Subscription</p>
              <p class="text-xs text-gray-500">Manage your plan</p>
            </div>
          </div>
          <ChevronRight class="w-5 h-5 text-gray-400" />
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '../../store/auth'
import { getUserActivityData } from '../../services/UserService'
import ActivityGraph from '../../components/ActivityGraph.vue'
import { Receipt, TrendingUp, Flame, Calendar, Settings, Crown, ChevronRight } from 'lucide-vue-next'

export default {
  name: 'ProfileSettingsView',
  components: {
    ActivityGraph,
    Receipt,
    TrendingUp,
    Flame,
    Calendar,
    Settings,
    Crown,
    ChevronRight
  },
  data() {
    return {
      loading: true,
      activityData: {},
      stats: {
        totalTransactions: 0,
        activeDays: 0,
        longestStreak: 0,
        avgPerDay: 0
      }
    }
  },
  computed: {
    ...mapState(useAuthStore, ['user']),
    userInitials() {
      if (!this.user?.name) return 'U'
      return this.user.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
    }
  },
  methods: {
    formatMemberSince(date) {
      if (!date) return 'N/A'
      return new Date(date).toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric'
      })
    },
    calculateStats() {
      const values = Object.values(this.activityData)
      const activeDays = values.filter(v => v > 0).length
      const totalTransactions = values.reduce((sum, v) => sum + v, 0)

      // Calculate longest streak
      let longestStreak = 0
      let currentStreak = 0
      const sortedDates = Object.keys(this.activityData).sort()

      for (let i = 0; i < sortedDates.length; i++) {
        if (this.activityData[sortedDates[i]] > 0) {
          currentStreak++
          longestStreak = Math.max(longestStreak, currentStreak)
        } else {
          currentStreak = 0
        }
      }

      this.stats = {
        totalTransactions,
        activeDays,
        longestStreak,
        avgPerDay: activeDays > 0 ? Math.round(totalTransactions / activeDays * 10) / 10 : 0
      }
    }
  },
  async created() {
    try {
      this.loading = true
      const data = await getUserActivityData()
      this.activityData = data.activity || {}
      this.calculateStats()
    } catch (error) {
      console.error('Failed to load activity data:', error)
      this.activityData = {}
    } finally {
      this.loading = false
    }
  }
}
</script>
