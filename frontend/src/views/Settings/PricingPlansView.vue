<template>
  <div class="max-w-4xl mx-auto p-2 mb-12 min-w-full">
    <!-- Current Plan Status -->
    <div class="bg-white rounded-lg shadow mb-4">
      <div class="p-4">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-xl font-semibold text-gray-900">Current Plan</h2>
            <p class="text-sm text-gray-600 mt-0.5">Manage your subscription</p>
          </div>
          <div class="flex items-center gap-2">
            <span class="px-2 py-0.5 rounded-full text-xs font-medium"
              :class="hasActiveSubscription ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'">
              {{ hasActiveSubscription ? 'Pro Plan Active' : 'Basic Plan' }}
            </span>
          </div>
        </div>

        <!-- Plan Cards -->
        <div class="flex flex-col md:flex-row md:grid md:grid-cols-2 gap-3">
          <!-- Basic Plan Card -->
          <div class="rounded-lg border p-4 flex flex-col justify-between"
            :class="!hasActiveSubscription ? 'bg-blue-50 border-blue-500 ring-1 ring-blue-500' : 'bg-gray-50 border-gray-200'">
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-medium text-gray-500">Basic</span>
                <span v-if="!hasActiveSubscription" class="text-blue-600 text-xs font-semibold">Current</span>
              </div>
              <h3 class="text-lg font-bold text-gray-900 mb-1">Basic Plan</h3>
              <p class="text-2xl font-bold text-gray-900 mb-2">$0<span
                  class="text-base font-normal text-gray-600">/month</span></p>
              <div class="space-y-3">
                <div class="flex items-start gap-3">
                  <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                  <p class="text-sm text-gray-600">Basic expense tracking and budgeting</p>
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
          <!-- Pro Plan Card -->
          <div class="rounded-lg border p-4 flex flex-col justify-between"
            :class="hasActiveSubscription ? 'bg-blue-50 border-blue-500 ring-1 ring-blue-500' : 'bg-gray-50 border-gray-200'">
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-medium"
                  :class="hasActiveSubscription ? 'text-blue-700' : 'text-gray-500'">Pro</span>
                <span v-if="hasActiveSubscription" class="text-blue-600 text-xs font-semibold">Current</span>
              </div>
              <h3 class="text-lg font-bold text-gray-900 mb-1">Pro Plan</h3>
              <p class="text-2xl font-bold text-gray-900 mb-2">$4.99<span
                  class="text-base font-normal text-gray-600">/month</span></p>
                  <div class="space-y-3">
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">Advanced expense analytics and insights (AI)</p>
                    </div>
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">AI Access (Know yourself better)</p>
                    </div>
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">Priority support and updates</p>
                    </div>
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">Request any new feature</p>
                    </div>
                      <div class="flex items-start gap-3">
                          <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                          <p class="text-sm text-gray-600">Free access to future updates</p>
                      </div>
                  </div>
            </div>
            <div class="mt-6 flex flex-col gap-3">
              <button v-if="!hasActiveSubscription" @click="handleSubscription"
                class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                :disabled="processingPayment">
                {{ processingPayment ? 'Processing...' : 'Upgrade Now' }}
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
                ${{ polarStore.subscription.items?.[0]?.amount || 4.99 }}
              </td>
              <td class="py-1.5 px-2">{{ polarStore.subscription.created_at ? new Date(polarStore.subscription.created_at).toLocaleDateString() : '' }}</td>
              <td class="py-1.5 px-2">
                <span :class="polarStore.subscription.status === 'active' ? 'text-blue-600' : 'text-red-500'">
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
      if (hasActiveSubscription.value) {
        notify({
          title: 'Subscription Active',
          message: 'You are currently on the Pro plan',
          type: 'success'
        })
      } else {
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

const handleSubscription = async () => {
  if (processingPayment.value) return
  processingPayment.value = true
  try {
    await polarStore.initiateCheckout()
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
