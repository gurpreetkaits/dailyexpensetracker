<template>
  <div class="bg-white shadow rounded-lg p-6">
    <div v-if="loading" class="text-center py-4">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500 mx-auto"></div>
    </div>
    
    <div v-else-if="subscription">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-lg font-medium text-gray-900">Current Plan</h3>
          <p class="text-sm text-gray-600">{{ subscription.plan_name }}</p>
        </div>
        <span
          :class="[
            'px-3 py-1 rounded-full text-sm font-medium',
            subscription.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-yellow-100 text-yellow-800'
          ]"
        >
          {{ subscription.status === 'active' ? 'Active' : 'Canceled' }}
        </span>
      </div>

      <div class="space-y-4">
        <div class="flex justify-between text-sm">
          <span class="text-gray-600">Next billing date</span>
          <span class="text-gray-900">{{ formatDate(subscription.current_period_end) }}</span>
        </div>

        <div v-if="subscription.canceled_at" class="flex justify-between text-sm">
          <span class="text-gray-600">Canceled on</span>
          <span class="text-gray-900">{{ formatDate(subscription.canceled_at) }}</span>
        </div>

        <div v-if="subscription.default_payment_method" class="mt-6">
          <h4 class="text-sm font-medium text-gray-900 mb-2">Payment Method</h4>
          <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span>•••• {{ subscription.default_payment_method.last4 }}</span>
            <span>{{ subscription.default_payment_method.brand }}</span>
            <span>Expires {{ subscription.default_payment_method.exp_month }}/{{ subscription.default_payment_method.exp_year }}</span>
          </div>
        </div>

        <div class="mt-6 space-y-3">
          <button
            v-if="subscription.status === 'active'"
            @click="handleCancelSubscription"
            class="w-full bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors"
            :disabled="processing"
          >
            {{ processing ? 'Processing...' : 'Cancel Subscription' }}
          </button>

          <button
            v-if="subscription.status === 'canceled' && subscription.cancel_at_period_end"
            @click="handleResumeSubscription"
            class="w-full bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 transition-colors"
            :disabled="processing"
          >
            {{ processing ? 'Processing...' : 'Resume Subscription' }}
          </button>

          <button
            @click="handleUpdatePaymentMethod"
            class="w-full bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors"
            :disabled="processing"
          >
            Update Payment Method
          </button>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-4">
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
import { ref, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(true)
const processing = ref(false)
const subscription = ref(null)

const loadSubscriptionStatus = async () => {
  try {
    const response = await axios.get('/api/subscription/status')
    subscription.value = response.data
  } catch (error) {
    console.error('Error loading subscription:', error)
  } finally {
    loading.value = false
  }
}

const handleCancelSubscription = async () => {
  if (!confirm('Are you sure you want to cancel your subscription?')) return
  
  processing.value = true
  try {
    await axios.post('/api/subscription/cancel')
    await loadSubscriptionStatus()
  } catch (error) {
    console.error('Error canceling subscription:', error)
  } finally {
    processing.value = false
  }
}

const handleResumeSubscription = async () => {
  processing.value = true
  try {
    await axios.post('/api/subscription/resume')
    await loadSubscriptionStatus()
  } catch (error) {
    console.error('Error resuming subscription:', error)
  } finally {
    processing.value = false
  }
}

const handleUpdatePaymentMethod = async () => {
  try {
    const response = await axios.post('/api/subscription/payment-method')
    window.location.href = response.data.url
  } catch (error) {
    console.error('Error updating payment method:', error)
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

onMounted(loadSubscriptionStatus)
</script> 