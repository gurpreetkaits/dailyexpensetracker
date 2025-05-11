<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center">
      <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Simple, transparent pricing</h2>
      <p class="mt-4 text-lg text-gray-600">Choose the plan that's right for you</p>
    </div>

    <div class="mt-12 grid gap-8 lg:grid-cols-3">
      <div v-for="plan in subscriptionPlans" :key="plan.id"
           :class="[
             'bg-white rounded-2xl shadow-sm border p-8',
             plan.id === 'price_pro' ? 'border-2 border-emerald-500 relative' : 'border-gray-100'
           ]">
        <div v-if="plan.id === 'price_pro'" class="absolute -top-4 left-1/2 transform -translate-x-1/2">
          <span class="bg-emerald-500 text-white px-4 py-1 rounded-full text-sm font-medium">Most Popular</span>
        </div>
        
        <h3 class="text-xl font-semibold text-gray-900">{{ plan.name }}</h3>
        <p class="mt-4 text-gray-600">{{ plan.description }}</p>
        <p class="mt-8">
          <span class="text-4xl font-bold text-gray-900">${{ plan.price }}</span>
          <span class="text-gray-600">/month</span>
        </p>
        
        <ul class="mt-8 space-y-4">
          <li v-for="feature in plan.features" :key="feature" class="flex items-center">
            <svg class="w-5 h-5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <span class="ml-3 text-gray-600">{{ feature }}</span>
          </li>
        </ul>
        
        <button
          @click="handleSubscribe(plan.id)"
          class="mt-8 w-full bg-emerald-500 text-white px-6 py-3 rounded-lg hover:bg-emerald-600 transition-colors"
          :disabled="loading"
        >
          {{ loading ? 'Processing...' : plan.price === 0 ? 'Get Started' : 'Subscribe Now' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useSubscriptionStore } from '../../store/subscription'

const subscriptionStore = useSubscriptionStore()
const loading = ref(false)

const subscriptionPlans = [
  {
    id: 'price_free',
    name: 'Free',
    description: 'Perfect for getting started',
    price: 0,
    features: [
      'Basic expense tracking',
      'Up to 50 transactions'
    ]
  },
  {
    id: 'price_pro',
    name: 'Pro',
    description: 'For serious expense tracking',
    price: 4.99,
    features: [
      'Unlimited transactions',
      'Advanced analytics',
      'Export to CSV',
      'Priority support'
    ]
  },
  {
    id: 'price_business',
    name: 'Business',
    description: 'For teams and businesses',
    price: 9.99,
    features: [
      'Everything in Pro',
      'Multiple users',
      'Team analytics',
      'Dedicated support'
    ]
  }
]

const handleSubscribe = async (priceId) => {
  loading.value = true
  try {
    await subscriptionStore.createSubscription(priceId)
  } catch (error) {
    console.error('Subscription error:', error)
    // Handle error (show notification, etc.)
  } finally {
    loading.value = false
  }
}
</script> 