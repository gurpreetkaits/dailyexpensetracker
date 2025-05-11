<template>
  <div class="relative">
    <!-- Premium Overlay - Only covers the content area -->
    <div v-if="!hasActiveSubscription" class="absolute inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="text-center">
          <div class="mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-[#10b981]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>
          <h2 class="text-xl font-semibold text-gray-800 mb-2">Premium Feature</h2>
          <p class="text-gray-600 mb-4">{{ description || 'This is a premium feature. Subscribe to unlock unlimited access.' }}</p>
          <button @click="showSubscriptionModal = true" 
                  class="bg-[#10b981] text-white px-6 py-2 rounded-lg hover:bg-green-600 transition-colors">
            Subscribe Now
          </button>
        </div>
      </div>
    </div>

    <!-- Subscription Modal - Higher z-index to appear above everything -->
    <div v-if="showSubscriptionModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-[100]">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold text-gray-800">Choose Your Plan</h3>
          <button @click="showSubscriptionModal = false" class="text-gray-500 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="space-y-4">
          <div v-for="plan in subscriptionPlans" :key="plan.id" 
               class="border rounded-lg p-4 cursor-pointer hover:border-[#10b981] transition-colors"
               :class="{'border-[#10b981]': selectedPlan === plan.id}"
               @click="selectedPlan = plan.id">
            <div class="flex justify-between items-center">
              <div>
                <h4 class="font-semibold text-gray-800">{{ plan.name }}</h4>
                <p class="text-gray-600 text-sm">{{ plan.description }}</p>
              </div>
              <div class="text-right">
                <p class="text-xl font-bold text-gray-800">${{ plan.price }}/mo</p>
                <p class="text-sm text-gray-500">Billed monthly</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6">
          <button @click="handleSubscription" 
                  class="w-full bg-[#10b981] text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors"
                  :disabled="!selectedPlan || processingPayment">
            {{ processingPayment ? 'Processing...' : 'Subscribe Now' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Content Slot - Only disabled when not subscribed -->
    <div :class="{'pointer-events-none': !hasActiveSubscription}">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useSubscriptionStore } from '../store/subscription'

const props = defineProps({
  description: {
    type: String,
    default: 'This is a premium feature. Subscribe to unlock unlimited access.'
  }
})

const subscriptionStore = useSubscriptionStore()
const hasActiveSubscription = computed(() => subscriptionStore.hasActiveSubscription)
const showSubscriptionModal = ref(false)
const selectedPlan = ref(null)
const processingPayment = ref(false)

const subscriptionPlans = [
  {
    id: 'premium_monthly',
    name: 'Premium Monthly',
    description: 'Unlimited access to all premium features',
    price: '9.99'
  }
]

const handleSubscription = async () => {
  if (!selectedPlan.value || processingPayment.value) return
  
  processingPayment.value = true
  try {
    await subscriptionStore.createSubscription(selectedPlan.value)
    showSubscriptionModal.value = false
  } catch (error) {
    console.error('Subscription error:', error)
  } finally {
    processingPayment.value = false
  }
}

// Fetch subscription status when component is mounted
subscriptionStore.fetchSubscriptionStatus()
</script>

<style scoped>
/* Ensure the overlay doesn't interfere with navigation */
.relative {
  position: relative;
  min-height: 100%;
}

/* Ensure the overlay respects the layout */
.absolute {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}
</style> 