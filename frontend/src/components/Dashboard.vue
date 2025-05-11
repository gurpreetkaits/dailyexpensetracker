<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Subscription Success Message -->
    <div v-if="showSuccessMessage" class="mb-8 bg-emerald-50 border border-emerald-200 rounded-lg p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-emerald-800">Subscription Activated</h3>
          <p class="text-sm text-emerald-700 mt-1">Thank you for subscribing! You now have access to all premium features.</p>
        </div>
        <div class="ml-auto pl-3">
          <button @click="showSuccessMessage = false" class="text-emerald-400 hover:text-emerald-500">
            <span class="sr-only">Dismiss</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Subscription Status -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-medium text-gray-900">Subscription Status</h2>
          <p class="text-sm text-gray-600">Manage your subscription and payment methods</p>
        </div>
        <div v-if="subscriptionStore.loading" class="animate-spin rounded-full h-5 w-5 border-b-2 border-emerald-500"></div>
      </div>

      <div v-if="subscriptionStore.subscription" class="mt-6 space-y-4">
        <div class="flex justify-between items-center">
          <div>
            <p class="text-sm font-medium text-gray-900">Current Plan</p>
            <p class="text-sm text-gray-600">Premium Plan</p>
          </div>
          <span :class="[
            'px-3 py-1 rounded-full text-sm font-medium',
            subscriptionStore.hasActiveSubscription ? 'bg-emerald-100 text-emerald-800' : 'bg-yellow-100 text-yellow-800'
          ]">
            {{ subscriptionStore.subscriptionStatus === 'active' ? 'Active' : 'Canceled' }}
          </span>
        </div>

        <div v-if="subscriptionStore.subscription.current_period_end" class="flex justify-between text-sm">
          <span class="text-gray-600">Next billing date</span>
          <span class="text-gray-900">{{ formatDate(subscriptionStore.subscription.current_period_end) }}</span>
        </div>

        <div v-if="subscriptionStore.subscription.canceled_at" class="flex justify-between text-sm">
          <span class="text-gray-600">Canceled on</span>
          <span class="text-gray-900">{{ formatDate(subscriptionStore.subscription.canceled_at) }}</span>
        </div>

        <div class="mt-6 space-y-3">
          <button
            v-if="subscriptionStore.hasActiveSubscription"
            @click="handleCancelSubscription"
            class="w-full bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors"
            :disabled="subscriptionStore.loading"
          >
            {{ subscriptionStore.loading ? 'Processing...' : 'Cancel Subscription' }}
          </button>

          <button
            v-if="subscriptionStore.subscriptionStatus === 'canceled' && subscriptionStore.subscription.cancel_at_period_end"
            @click="handleResumeSubscription"
            class="w-full bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 transition-colors"
            :disabled="subscriptionStore.loading"
          >
            {{ subscriptionStore.loading ? 'Processing...' : 'Resume Subscription' }}
          </button>
        </div>
      </div>

      <div v-else class="mt-6 text-center py-4">
        <p class="text-gray-600">No active subscription</p>
        <router-link
          to="/pricing"
          class="mt-4 inline-block bg-emerald-500 text-white px-6 py-2 rounded-lg hover:bg-emerald-600 transition-colors"
        >
          View Plans
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useSubscriptionStore } from '../store/subscription'
import { useNotifications } from '../composables/useNotifications'

defineOptions({
  name: 'SubscriptionDashboard'
})

const route = useRoute()
const subscriptionStore = useSubscriptionStore()
const { notify } = useNotifications()
const showSuccessMessage = ref(false)

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const handleCancelSubscription = async () => {
  if (!confirm('Are you sure you want to cancel your subscription?')) return
  
  try {
    await subscriptionStore.cancelSubscription()
    notify({
      title: 'Success',
      message: 'Your subscription has been canceled',
      type: 'success'
    })
  } catch (error) {
    notify({
      title: 'Error',
      message: error.message || 'Failed to cancel subscription',
      type: 'error'
    })
  }
}

const handleResumeSubscription = async () => {
  try {
    await subscriptionStore.resumeSubscription()
    notify({
      title: 'Success',
      message: 'Your subscription has been resumed',
      type: 'success'
    })
  } catch (error) {
    notify({
      title: 'Error',
      message: error.message || 'Failed to resume subscription',
      type: 'error'
    })
  }
}

onMounted(async () => {
  // Check for session_id in URL
  const sessionId = route.query.session_id
  if (sessionId) {
    try {
      const verified = await subscriptionStore.verifyCheckoutSession(sessionId)
      if (verified) {
        showSuccessMessage.value = true
        // Remove session_id from URL without refreshing
        window.history.replaceState({}, document.title, '/dashboard')
      }
    } catch (error) {
      notify({
        title: 'Error',
        message: 'Failed to verify subscription status',
        type: 'error'
      })
    }
  } else {
    // Regular subscription status check
    await subscriptionStore.fetchSubscriptionStatus()
  }
})
</script> 