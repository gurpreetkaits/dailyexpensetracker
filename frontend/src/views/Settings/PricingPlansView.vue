<template>
  <div class="max-w-5xl mx-auto p-4 mb-12 min-w-full">
    <!-- Current Plan Status -->
    <div class="bg-white rounded-lg shadow mb-6">
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Choose Your Plan</h2>
            <p class="text-sm text-gray-600 mt-1">Select the plan that best fits your needs</p>
          </div>
          <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-sm font-medium"
              :class="hasActiveSubscription ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'">
              {{ hasActiveSubscription ? 'Pro Plan Active' : 'Basic Plan' }}
            </span>
          </div>
        </div>

        <!-- Plan Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Basic Plan Card -->
          <div class="rounded-xl border p-6 flex flex-col justify-between transition-all duration-200 hover:shadow-lg"
            :class="!hasActiveSubscription ? 'bg-blue-50 border-blue-500 ring-1 ring-blue-500' : 'bg-gray-50 border-gray-200'">
            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">Basic</span>
                <span v-if="!hasActiveSubscription" class="text-blue-600 text-sm font-semibold">Current</span>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Basic Plan</h3>
              <p class="text-3xl font-bold text-gray-900 mb-1">$0<span class="text-base font-normal text-gray-600">/month</span></p>
              <p class="text-sm text-gray-500 mb-4">Perfect for getting started</p>
              <div class="space-y-3">
                <div class="flex items-start gap-3">
                  <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                  <p class="text-sm text-gray-600">Basic expense tracking</p>
                </div>
                <div class="flex items-start gap-3">
                  <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                  <p class="text-sm text-gray-600">Default categories</p>
                </div>
                <div class="flex items-start gap-3">
                  <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                  <p class="text-sm text-gray-600">Basic monthly reports</p>
                </div>
                <div class="flex items-start gap-3">
                  <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                  <p class="text-sm text-gray-600">Single wallet</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Monthly Pro Plan Card -->
          <div class="rounded-xl border p-6 flex flex-col justify-between transition-all duration-200 hover:shadow-lg relative"
            :class="hasActiveSubscription ? 'bg-blue-50 border-blue-500 ring-1 ring-blue-500' : 'bg-gray-50 border-gray-200'">
            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
              <span class="bg-blue-600 text-white text-xs font-semibold px-3 py-1 rounded-full">Most Popular</span>
            </div>
            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium" :class="hasActiveSubscription ? 'text-blue-700' : 'text-gray-500'">Pro Monthly</span>
                <span v-if="hasActiveSubscription" class="text-blue-600 text-sm font-semibold">Current</span>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Pro Plan</h3>
              <p class="text-3xl font-bold text-gray-900 mb-1">${{ monthlyPlanPrice }}<span class="text-base font-normal text-gray-600">/month</span></p>
              <p class="text-sm text-gray-500 mb-4">Billed monthly</p>
              <div class="space-y-3">
                <div class="flex items-start gap-3">
                  <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                  <p class="text-sm text-gray-600">Advanced AI-powered analytics</p>
                </div>
                <div class="flex items-start gap-3">
                  <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                  <p class="text-sm text-gray-600">Weekly/Monthly Email Reports</p>
                </div>
                <div class="flex items-start gap-3">
                  <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                  <p class="text-sm text-gray-600">Import Statements (Excel)</p>
                </div>
                <div class="flex items-start gap-3">
                  <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                  <p class="text-sm text-gray-600">Priority support</p>
                </div>
                <div class="flex items-start gap-3">
                  <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                  <p class="text-sm text-gray-600">Free future updates</p>
                </div>
              </div>
            </div>
            <div class="mt-6">
              <button v-if="!hasActiveSubscription" @click="handleSubscription('monthly')"
                class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors"
                :disabled="processingPayment">
                {{ processingPayment ? 'Processing...' : 'Start Monthly Plan' }}
              </button>
              <button v-else @click="handleManageSubscription"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Manage Subscription
              </button>
            </div>
          </div>

          <!-- Yearly Pro Plan Card -->
          <div class="rounded-xl border p-6 flex flex-col justify-between transition-all duration-200 hover:shadow-lg relative"
            :class="hasActiveSubscription ? 'bg-blue-50 border-blue-500 ring-1 ring-blue-500' : 'bg-gray-50 border-gray-200'">
            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
              <span class="bg-green-600 text-white text-xs font-semibold px-3 py-1 rounded-full">Save 17%</span>
            </div>
            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium" :class="hasActiveSubscription ? 'text-blue-700' : 'text-gray-500'">Pro Yearly</span>
                <span v-if="hasActiveSubscription" class="text-blue-600 text-sm font-semibold">Current</span>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Pro Plan</h3>
              <div class="flex items-baseline gap-1 mb-1">
                <p class="text-3xl font-bold text-gray-900">${{ yearlyPlanPrice }}</p>
                <p class="text-base font-normal text-gray-600">/year</p>
              </div>
              <p class="text-sm text-gray-500 mb-4">Just ${{ (yearlyPlanPrice / 12).toFixed(2) }}/month, billed annually</p>
              <div class="space-y-3">
                <div class="flex items-start gap-3">
                  <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                  <p class="text-sm text-gray-600">All Monthly Pro features</p>
                </div>
                <div class="flex items-start gap-3">
                  <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                  <p class="text-sm text-gray-600">2 months free</p>
                </div>
                <div class="flex items-start gap-3">
                  <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                  <p class="text-sm text-gray-600">Priority feature requests</p>
                </div>
                <div class="flex items-start gap-3">
                  <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                  <p class="text-sm text-gray-600">Early access to new features</p>
                </div>
                <div class="flex items-start gap-3">
                  <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                  <p class="text-sm text-gray-600">Free future updates</p>
                </div>
              </div>
            </div>
            <div class="mt-6">
              <button v-if="!hasActiveSubscription" @click="handleSubscription('yearly')"
                class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 transition-colors"
                :disabled="processingPayment">
                {{ processingPayment ? 'Processing...' : 'Start Yearly Plan' }}
              </button>
              <button v-else @click="handleManageSubscription"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                Manage Subscription
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Purchase History -->
    <div class="bg-white rounded-lg shadow p-3 mt-4">
      <h2 class="text-base font-semibold text-gray-900 mb-3">Purchase History</h2>
      <div class="overflow-x-auto -mx-3">
        <table class="w-full text-xs text-left">
          <thead>
            <tr class="border-b text-gray-500">
              <th class="py-1.5 px-2">Plan</th>
              <th class="py-1.5 px-2">Amount</th>
              <th class="py-1.5 px-2">Date</th>
              <th class="py-1.5 px-2">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="polarStore.subscription" class="border-b last:border-0">
              <td class="py-1.5 px-2">Pro Plan</td>
              <td class="py-1.5 px-2">
                ${{ polarStore.subscription.amount / 100 || 4.99 }}
              </td>
              <td class="py-1.5 px-2">{{ polarStore.subscription.created_at ? new Date(polarStore.subscription.created_at).toLocaleDateString() : '' }}</td>
              <td class="py-1.5 px-2">
                <span :class="polarStore.subscription.status === 'active' ? 'text-blue-600 capitalize' : 'text-red-500 capitalize'">
                  {{ polarStore.subscription.status }}
                </span>
              </td>
            </tr>
            <tr v-if="!polarStore.subscription">
              <td colspan="4" class="text-center text-gray-400 py-3">No purchase history found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePolarStore } from '../../store/polar'
import { useNotifications } from '../../composables/useNotifications'
import { CheckCircle } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const polarStore = usePolarStore()
const { notify } = useNotifications()
const hasActiveSubscription = computed(() => polarStore.hasActiveSubscription)
const processingPayment = ref(false)

const monthlyPlanPrice = 2.99
const yearlyPlanPrice = 29.9

const currentPage = ref(1)
const totalPages = computed(() => polarStore.subscription?.total_pages || 1)
const startIndex = computed(() => polarStore.subscription?.from || 0)
const endIndex = computed(() => polarStore.subscription?.to || 0)
const totalItems = computed(() => polarStore.subscription?.total || 0)

const displayedPages = computed(() => {
  const pages = []
  const maxVisiblePages = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxVisiblePages / 2))
  let end = Math.min(totalPages.value, start + maxVisiblePages - 1)
  if (end - start + 1 < maxVisiblePages) {
    start = Math.max(1, end - maxVisiblePages + 1)
  }
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

const handlePageChange = async (page) => {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
}

onMounted(async () => {
  try {
    const route = useRoute()
    const checkoutId = route.query?.checkout_id
    if (checkoutId) {
      await polarStore.verifyCheckoutSession(checkoutId)
      console.log('polarStore.hasActiveSubscription', polarStore.hasActiveSubscription)
      if (polarStore.hasActiveSubscription) {
        notify({
          title: 'Subscription Activated',
          message: 'Welcome to the Pro plan!',
          type: 'success'
        })
      } else {
        notify({
          title: 'Subscription Failed',
          message: 'Failed to activate subscription. Please try again.',
          type: 'error'
        })
      }
    } else {
      await polarStore.fetchSubscriptionStatus()
      if (!hasActiveSubscription.value) {
        notify({
          title: 'Basic Plan',
          message: 'Upgrade to Pro for advanced features',
          type: 'info'
        })
      }
    }
  } catch (error) {
    console.error('Subscription status error:', error)
    notify({
      title: 'Error',
      message: error.response?.data?.error || 'Failed to fetch subscription status',
      type: 'error'
    })
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
      title: 'Upgrade Failed',
      message: error.response?.data?.error || 'Failed to start subscription process',
      type: 'error'
    })
  } finally {
    processingPayment.value = false
  }
}

const handleManageSubscription = () => {
  try {
    notify({
      title: 'Coming Soon',
      message: 'Subscription management portal will be available soon',
      type: 'info'
    })
  } catch (error) {
    console.error('Manage subscription error:', error)
    notify({
      title: 'Error',
      message: 'Failed to manage subscription',
      type: 'error'
    })
  }
}
</script>

<style scoped>
.flex::-webkit-scrollbar {
  display: none;
}

.flex {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
