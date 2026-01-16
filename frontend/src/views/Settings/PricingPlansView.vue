<template>
  <div class="min-h-screen bg-gray-100 pb-24">
    <div class="px-4 pt-6 max-w-lg mx-auto">
      <!-- Pro Plan Card -->
      <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden relative">
        <!-- Popular Badge -->
        <div class="absolute top-0 right-0 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-xs font-bold px-4 py-1.5 rounded-bl-2xl">
          BEST VALUE
        </div>

        <div class="p-6 pt-8">
          <!-- Plan Header -->
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-2xl flex items-center justify-center">
              <Crown class="w-6 h-6 text-white" />
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-900">Pro Plan</h2>
              <p class="text-sm text-gray-500">Everything you need</p>
            </div>
          </div>

          <!-- Pricing -->
          <div class="mb-6">
            <div class="flex items-baseline gap-1">
              <span class="text-4xl font-bold text-gray-900">{{ formatPrice(yearlyPrice) }}</span>
              <span class="text-gray-500">/year</span>
            </div>
            <p class="text-sm text-gray-500 mt-1">
              Just <span class="font-semibold text-emerald-600">{{ formatPrice(monthlyEquivalent) }}/month</span>
            </p>
          </div>

          <!-- Features List -->
          <div class="space-y-3 mb-6">
            <div v-for="feature in proFeatures" :key="feature.title" class="flex items-start gap-3">
              <div class="w-5 h-5 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                <Check class="w-3 h-3 text-emerald-600" />
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">{{ feature.title }}</p>
                <p class="text-xs text-gray-500">{{ feature.description }}</p>
              </div>
            </div>
          </div>

          <!-- CTA Button -->
          <button
            v-if="!hasActiveSubscription"
            @click="handleSubscription('yearly')"
            :disabled="processingPayment"
            class="w-full py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:shadow-emerald-500/40 transition-all duration-300 active:scale-[0.98]"
          >
            <span v-if="processingPayment" class="flex items-center justify-center gap-2">
              <Loader2 class="w-4 h-4 animate-spin" />
              Processing...
            </span>
            <span v-else class="flex items-center justify-center gap-2">
              Start 7-Day Free Trial
              <ArrowRight class="w-4 h-4" />
            </span>
          </button>

          <!-- Already Subscribed -->
          <div v-else class="text-center">
            <div class="inline-flex items-center gap-2 bg-emerald-100 text-emerald-700 px-4 py-2 rounded-full text-sm font-medium mb-3">
              <CheckCircle class="w-4 h-4" />
              You're on Pro Plan
            </div>
            <button
              @click="handleManageSubscription"
              class="w-full py-3 border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
            >
              Manage Subscription
            </button>
          </div>

          <p class="text-center text-xs text-gray-400 mt-3">
            Cancel anytime • No questions asked
          </p>
        </div>
      </div>

      <!-- Free Plan Comparison -->
      <div class="mt-6 bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="font-semibold text-gray-900">Free Plan</h3>
            <p class="text-sm text-gray-500">Basic tracking</p>
          </div>
          <span class="text-lg font-bold text-gray-400">{{ currencySymbol }}0</span>
        </div>
        <div class="space-y-2">
          <div v-for="feature in freeFeatures" :key="feature.text" class="flex items-center gap-2 text-sm">
            <component :is="feature.included ? Check : X"
              :class="feature.included ? 'text-gray-400' : 'text-gray-300'"
              class="w-4 h-4"
            />
            <span :class="feature.included ? 'text-gray-600' : 'text-gray-400'">{{ feature.text }}</span>
            <span v-if="feature.limit" class="text-xs text-gray-400">({{ feature.limit }})</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePolarStore } from '../../store/polar'
import { useSettingsStore } from '../../store/settings'
import { useNotifications } from '../../composables/useNotifications'
import {
  Crown, Check, X, ArrowRight, Loader2, CheckCircle
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const polarStore = usePolarStore()
const settingsStore = useSettingsStore()
const { notify } = useNotifications()

const hasActiveSubscription = computed(() => polarStore.hasActiveSubscription)
const processingPayment = ref(false)

// Base price in USD
const basePriceUSD = 12 // $12/year

// Timezone to currency mapping
const timezoneCurrencyMap = {
  'Asia/Kolkata': 'INR',
  'Asia/Mumbai': 'INR',
  'Asia/Calcutta': 'INR',
  'America/New_York': 'USD',
  'America/Los_Angeles': 'USD',
  'America/Chicago': 'USD',
  'Europe/London': 'GBP',
  'Europe/Paris': 'EUR',
  'Europe/Berlin': 'EUR',
  'Europe/Amsterdam': 'EUR',
  'Asia/Tokyo': 'JPY',
  'Asia/Singapore': 'SGD',
  'Asia/Dubai': 'AED',
  'Australia/Sydney': 'AUD',
  'America/Toronto': 'CAD',
  'Asia/Shanghai': 'CNY',
}

// Exchange rates (approximate)
const exchangeRates = {
  USD: 1,
  INR: 83,
  EUR: 0.92,
  GBP: 0.79,
  CAD: 1.36,
  AUD: 1.53,
  JPY: 149,
  CNY: 7.24,
  SGD: 1.34,
  AED: 3.67
}

// Detect currency from timezone
const detectCurrencyFromTimezone = () => {
  const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone
  return timezoneCurrencyMap[timezone] || 'USD'
}

// Currency settings - prefer settings, fallback to timezone detection
const currencyCode = computed(() => settingsStore.currencyCode || detectCurrencyFromTimezone())
const currencySymbol = computed(() => {
  const symbols = { USD: '$', INR: '₹', EUR: '€', GBP: '£', CAD: 'C$', AUD: 'A$', JPY: '¥', CNY: '¥', SGD: 'S$', AED: 'د.إ' }
  return settingsStore.currencySymbol || symbols[currencyCode.value] || '$'
})

const yearlyPrice = computed(() => {
  const rate = exchangeRates[currencyCode.value] || 1
  return Math.round(basePriceUSD * rate)
})

const monthlyEquivalent = computed(() => {
  return Math.round(yearlyPrice.value / 12)
})

const formatPrice = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currencyCode.value,
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

const proFeatures = [
  { title: 'AI-Powered Insights', description: 'Weekly analysis of your spending patterns' },
  { title: 'Unlimited Exports', description: 'Export reports anytime, no limits' },
  { title: 'Full History Access', description: 'View all your past transactions' },
  { title: 'Investment Tracker', description: 'Track Gold, Silver & Mutual Funds' },
  { title: 'Priority Support', description: 'Get help when you need it' },
]

const freeFeatures = [
  { text: 'Basic expense tracking', included: true },
  { text: 'Wallets & Categories', included: true },
  { text: 'Export/Import', included: true, limit: '5/year' },
  { text: 'History', included: true, limit: '3 months' },
  { text: 'AI Insights', included: false },
  { text: 'Investment Tracker', included: false },
]

onMounted(async () => {
  try {
    await settingsStore.fetchSettings()

    const checkoutId = route.query?.checkout_id
    if (checkoutId) {
      await polarStore.verifyCheckoutSession(checkoutId)
      if (polarStore.hasActiveSubscription) {
        notify({
          title: 'Welcome to Pro!',
          message: 'Your subscription is now active',
          type: 'success'
        })
        router.replace({ query: {} })
      }
    } else {
      await polarStore.fetchSubscriptionStatus()
    }
  } catch (error) {
    console.error('Subscription status error:', error)
  }
})

const handleSubscription = async (planType) => {
  if (processingPayment.value) return
  processingPayment.value = true
  try {
    await polarStore.initiateCheckout(planType)
  } catch (error) {
    console.error('Subscription error:', error)
    notify({
      title: 'Error',
      message: error.response?.data?.error || 'Failed to start checkout',
      type: 'error'
    })
  } finally {
    processingPayment.value = false
  }
}

const handleManageSubscription = () => {
  notify({
    title: 'Coming Soon',
    message: 'Subscription management is coming soon',
    type: 'info'
  })
}
</script>
