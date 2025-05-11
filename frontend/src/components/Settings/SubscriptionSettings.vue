<template>
  <div class="bg-white shadow rounded-lg p-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-medium text-gray-900">Subscription</h2>
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

        <button
          v-if="subscriptionStore.hasActiveSubscription"
          @click="handleUpdatePaymentMethod"
          class="w-full bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors"
          :disabled="subscriptionStore.loading"
        >
          Update Payment Method
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
</template>

<script setup>
import { onMounted } from 'vue'
import { useSubscriptionStore } from '../../store/subscription'

const subscriptionStore = useSubscriptionStore()

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const handleCancelSubscription = async () => {
  if (!confirm('Are you sure you want to cancel your subscription?')) return
  await subscriptionStore.cancelSubscription()
}

const handleResumeSubscription = async () => {
  await subscriptionStore.resumeSubscription()
}

const handleUpdatePaymentMethod = async () => {
  await subscriptionStore.updatePaymentMethod()
}

onMounted(async () => {
  await subscriptionStore.fetchSubscriptionStatus()
})
</script> 