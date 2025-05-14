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
              :class="hasActiveSubscription ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800'">
              {{ hasActiveSubscription ? 'Pro Plan Active' : 'Basic Plan' }}
            </span>
          </div>
        </div>

        <!-- Plan Cards -->
        <div class="flex flex-col md:flex-row md:grid md:grid-cols-2 gap-3">
          <!-- Basic Plan Card -->
          <div
            class="rounded-lg border p-4 flex flex-col justify-between bg-emerald-50 md:min-w-0"
            :class="!hasActiveSubscription ? 'border-emerald-500 ring-1 ring-emerald-500' : 'border-gray-200'"
          >
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-medium text-gray-500">Basic</span>
                <span v-if="!hasActiveSubscription" class="text-emerald-600 text-xs font-semibold">Current</span>
              </div>
              <h3 class="text-lg font-bold text-gray-900 mb-1">Basic Plan</h3>
              <p class="text-2xl font-bold text-gray-900 mb-2">$0<span class="text-base font-normal text-gray-600">/month</span></p>
              <ul class="space-y-1 text-xs text-gray-700 mb-3">
                <li>Basic expense tracking and budgeting</li>
                <li>Default categories</li>
                <li>Basic monthly reports</li>
                <li>Single wallet</li>
              </ul>
            </div>
          </div>
          <!-- Pro Plan Card -->
          <div
            class="rounded-lg border p-4 flex flex-col justify-between bg-emerald-50 md:min-w-0"
            :class="hasActiveSubscription ? 'border-emerald-500 ring-1 ring-emerald-200' : 'border-gray-200'"
          >
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-medium text-emerald-700">Pro</span>
                <span v-if="hasActiveSubscription" class="text-emerald-600 text-xs font-semibold">Current</span>
              </div>
              <h3 class="text-lg font-bold text-gray-900 mb-1">Pro Plan</h3>
              <p class="text-2xl font-bold text-gray-900 mb-2">$7<span class="text-base font-normal text-gray-600">/month</span></p>
              <ul class="space-y-1 text-xs text-gray-700 mb-3">
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
                class="w-full bg-emerald-500 text-white px-3 py-1.5 text-sm rounded-lg hover:bg-emerald-600 transition-colors"
                :disabled="processingPayment">
                {{ processingPayment ? 'Processing...' : 'Upgrade to Pro' }}
              </button>
              <button v-else
                @click="handleManageSubscription"
                class="w-full bg-gray-100 text-gray-700 px-3 py-1.5 text-sm rounded-lg hover:bg-gray-200 transition-colors">
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
            <tr v-for="item in subscriptionStore.subscriptionHistory" :key="item.id" class="border-b last:border-0">
              <td class="py-1.5 px-2">{{ item.name || item.plan_id || 'Pro Plan' }}</td>
              <td class="py-1.5 px-2">${{ item.price ?? 0 }}</td>
              <td class="py-1.5 px-2">{{ item.created_at ? new Date(item.created_at).toLocaleDateString() : '' }}</td>
              <td class="py-1.5 px-2">
                <span :class="item.status === 'active' ? 'text-emerald-600' : 'text-gray-500'">{{ item.status }}</span>
              </td>
            </tr>
            <tr v-if="!subscriptionStore.subscriptionHistory.length">
              <td colspan="4" class="text-center text-gray-400 py-3">No purchase history found.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination Controls -->
      <div v-if="totalPages > 1" class="mt-3 flex items-center justify-between">
        <div class="text-xs text-gray-500">
          {{ startIndex }}-{{ endIndex }} of {{ totalItems }}
        </div>
        <div class="flex gap-1">
          <button @click="handlePageChange(currentPage - 1)"
                  :disabled="currentPage === 1"
                  class="px-2 py-0.5 rounded border text-xs disabled:opacity-50"
                  :class="currentPage === 1 ? 'text-gray-400' : 'text-gray-700 hover:bg-gray-50'">
            Prev
          </button>
          <button v-for="page in displayedPages"
                  :key="page"
                  @click="handlePageChange(page)"
                  class="px-2 py-0.5 rounded border text-xs"
                  :class="currentPage === page ? 'bg-emerald-500 text-white border-emerald-500' : 'text-gray-700 hover:bg-gray-50'">
            {{ page }}
          </button>
          <button @click="handlePageChange(currentPage + 1)"
                  :disabled="currentPage === totalPages"
                  class="px-2 py-0.5 rounded border text-xs disabled:opacity-50"
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

const route = useRoute()
const router = useRouter()
const subscriptionStore = useSubscriptionStore()
const { notify } = useNotifications()
const hasActiveSubscription = computed(() => subscriptionStore.hasActiveSubscription)
const processingPayment = ref(false)

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
  await subscriptionStore.fetchSubscriptionStatus()
  await subscriptionStore.fetchSubscriptionHistory(currentPage.value)

  const checkoutId = route.query.checkout_id
  if (checkoutId) {
    try {
      const verified = await subscriptionStore.verifyCheckoutSession(checkoutId)
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
  if (processingPayment.value) return
  processingPayment.value = true
  try {
    const response = await subscriptionStore.createSubscription()
    if (!response.checkout_url) {
      throw new Error('Unable to start subscription process. Please try again.')
    }
    window.location.href = response.checkout_url
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
