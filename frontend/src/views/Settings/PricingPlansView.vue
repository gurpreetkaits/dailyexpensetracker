<template>
  <div class="max-w-4xl mx-auto p-4 min-h-[80vh]">
    <!-- Current Plan Status -->
    <div class="bg-white rounded-lg shadow mb-8">
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-2xl font-semibold text-gray-900">Current Plan</h2>
            <p class="text-gray-600 mt-1">Manage your subscription and billing</p>
          </div>
          <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-sm font-medium"
              :class="hasActiveSubscription ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800'">
              {{ hasActiveSubscription ? 'Pro Plan Active' : 'Basic Plan' }}
            </span>
          </div>
        </div>

        <!-- Plan Cards -->
        <div class="flex md:grid md:grid-cols-2 gap-4 overflow-x-auto md:overflow-visible pb-4">
          <!-- Basic Plan Card -->
          <div
            class="min-w-[260px] md:min-w-0 flex-1 rounded-xl border p-6 flex flex-col justify-between mr-3 md:mr-0 bg-emerald-50"
            :class="!hasActiveSubscription ? 'border-emerald-500 ring-2 ring-emerald-500' : 'border-gray-200'"
          >
            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-gray-500">Basic</span>
                <span v-if="!hasActiveSubscription" class="text-emerald-600 text-xs font-semibold">Current</span>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Basic Plan</h3>
              <p class="text-3xl font-bold text-gray-900 mb-4">$0<span class="text-lg font-normal text-gray-600">/month</span></p>
              <ul class="space-y-2 text-sm text-gray-700 mb-4">
                <li>Basic expense tracking and budgeting</li>
                <li>Default categories</li>
                <li>Basic monthly reports</li>
                <li>Single wallet</li>
              </ul>
            </div>
          </div>
          <!-- Pro Plan Card -->
          <div
            class="min-w-[260px] md:min-w-0 flex-1 rounded-xl border p-6 flex flex-col justify-between bg-emerald-50"
            :class="hasActiveSubscription ? 'border-emerald-500 ring-2 ring-emerald-200' : 'border-gray-200'"
          >
            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-emerald-700">Pro</span>
                <span v-if="hasActiveSubscription" class="text-emerald-600 text-xs font-semibold">Current</span>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Pro Plan</h3>
              <p class="text-3xl font-bold text-gray-900 mb-4">$7<span class="text-lg font-normal text-gray-600">/month</span></p>
              <ul class="space-y-2 text-sm text-gray-700 mb-4">
                <li>AI-powered analytics</li>
                <li>AI Chat (Know yourself better)</li>
                <li>Custom categories</li>
                <li>Multiple wallets</li>
                <li>Import bank statements (soon)</li>
              </ul>
            </div>
            <div class="mt-2">
              <button v-if="!hasActiveSubscription"
                @click="handleSubscription"
                class="w-full bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 transition-colors"
                :disabled="processingPayment">
                {{ processingPayment ? 'Processing...' : 'Upgrade to Pro' }}
              </button>
              <button v-else
                @click="handleManageSubscription"
                class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors">
                Manage Subscription
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Purchase History -->
    <div class="bg-white rounded-xl shadow p-4 mt-6 pb-24">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Purchase History</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead>
            <tr class="border-b text-gray-500">
              <th class="py-2 px-2">Plan Name</th>
              <th class="py-2 px-2">Amount</th>
              <th class="py-2 px-2">Purchase Date</th>
              <th class="py-2 px-2">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in subscriptionStore.subscriptionHistory" :key="item.id" class="border-b last:border-0">
              <td class="py-2 px-2">{{ item.name || item.plan || 'Pro Plan' }}</td>
              <td class="py-2 px-2">${{ item.price ?? 0 }}</td>
              <td class="py-2 px-2">{{ item.created_at ? new Date(item.created_at).toLocaleDateString() : '' }}</td>
              <td class="py-2 px-2">
                <span :class="item.stripe_status === 'active' ? 'text-emerald-600' : 'text-gray-500'">{{ item.stripe_status }}</span>
              </td>
            </tr>
            <tr v-if="!subscriptionStore.subscriptionHistory.length">
              <td colspan="4" class="text-center text-gray-400 py-4">No purchase history found.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination Controls -->
      <div v-if="totalPages > 1" class="mt-4 flex items-center justify-between">
        <div class="text-sm text-gray-500">
          Showing {{ startIndex }}-{{ endIndex }} of {{ totalItems }}
        </div>
        <div class="flex gap-1">
          <button @click="handlePageChange(currentPage - 1)"
                  :disabled="currentPage === 1"
                  class="px-2 py-1 rounded border text-sm disabled:opacity-50"
                  :class="currentPage === 1 ? 'text-gray-400' : 'text-gray-700 hover:bg-gray-50'">
            Previous
          </button>
          <button v-for="page in displayedPages"
                  :key="page"
                  @click="handlePageChange(page)"
                  class="px-2 py-1 rounded border text-sm"
                  :class="currentPage === page ? 'bg-emerald-500 text-white border-emerald-500' : 'text-gray-700 hover:bg-gray-50'">
            {{ page }}
          </button>
          <button @click="handlePageChange(currentPage + 1)"
                  :disabled="currentPage === totalPages"
                  class="px-2 py-1 rounded border text-sm disabled:opacity-50"
                  :class="currentPage === totalPages ? 'text-gray-400' : 'text-gray-700 hover:bg-gray-50'">
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSubscriptionStore } from '../../store/subscription'
import { useNotifications } from '../../composables/useNotifications'
import { loadStripe } from '@stripe/stripe-js'

const route = useRoute()
const router = useRouter()
const subscriptionStore = useSubscriptionStore()
const { notify } = useNotifications()
const hasActiveSubscription = computed(() => subscriptionStore.hasActiveSubscription)
const processingPayment = ref(false)
const stripe = ref(null)

const currentPage = ref(1)
const totalPages = computed(() => subscriptionStore.pagination.last_page)
const startIndex = computed(() => subscriptionStore.pagination.from)
const endIndex = computed(() => subscriptionStore.pagination.to)
const totalItems = computed(() => subscriptionStore.pagination.total)
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
  await subscriptionStore.fetchSubscriptionHistory(page)
}

onMounted(async () => {
  stripe.value = await loadStripe(import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY)
  await subscriptionStore.fetchSubscriptionStatus()
  await subscriptionStore.fetchSubscriptionHistory(currentPage.value)

  const sessionId = route.query.session_id
  if (sessionId) {
    try {
      const verified = await subscriptionStore.verifyCheckoutSession(sessionId)
      if (verified) {
        notify({
          title: 'Success',
          message: 'Premium features activated successfully',
          type: 'success'
        })
        router.replace({ path: route.path })
      }
    } catch (error) {
      console.error('Failed to verify subscription:', error)
      notify({
        title: 'Error',
        message: 'Failed to verify subscription',
        type: 'error'
      })
    }
  }
})

const handleSubscription = async () => {
  if (processingPayment.value || !stripe.value) return
  processingPayment.value = true
  try {
    const response = await subscriptionStore.createSubscription()
    if (!response.session_id) {
      throw new Error('Unable to start subscription process. Please try again.')
    }
    const { error } = await stripe.value.redirectToCheckout({
      sessionId: response.session_id,
    })
    if (error) {
      throw new Error(error.message || 'An unexpected error occurred')
    }
  } catch (error) {
    console.error('Subscription error:', error)
    notify({
      title: 'Error',
      message: error.message || 'Failed to start subscription process',
      type: 'error'
    })
  } finally {
    processingPayment.value = false
  }
}

const handleManageSubscription = () => {
  notify({
    title: 'Coming Soon',
    message: 'Subscription management portal will be available soon',
    type: 'info'
  })
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
