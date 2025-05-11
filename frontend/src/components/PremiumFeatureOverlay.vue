<template>
  <div class="relative">
    <!-- Premium Overlay -->
    <div v-if="!hasActiveSubscription" class="absolute inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="text-center">
          <div class="mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>
          <h2 class="text-xl font-semibold text-gray-800 mb-2">Premium Feature</h2>
          <p class="text-gray-600 mb-4">{{ description }}</p>
          <div class="mb-6">
            <p class="text-2xl font-bold text-gray-900">$7<span class="text-lg font-normal text-gray-600">/month</span></p>
            <p class="text-sm text-gray-600 mt-1">Unlock all premium features</p>
          </div>
          <button @click="handleSubscription"
                  class="w-full bg-emerald-500 text-white px-6 py-3 rounded-lg hover:bg-emerald-600 transition-colors"
                  :disabled="processingPayment">
            {{ processingPayment ? 'Processing...' : 'Subscribe Now' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Content Slot -->
    <div :class="{'pointer-events-none': !hasActiveSubscription}">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useSubscriptionStore } from '../store/subscription'
import { useNotifications } from '../composables/useNotifications'
import { loadStripe } from '@stripe/stripe-js'

const props = defineProps({
  description: {
    type: String,
    default: 'This is a premium feature. Subscribe to unlock unlimited access.'
  }
})

const route = useRoute()
const subscriptionStore = useSubscriptionStore()
const { notify } = useNotifications()
const hasActiveSubscription = computed(() => subscriptionStore.hasActiveSubscription)
const processingPayment = ref(false)
const stripe = ref(null)

onMounted(async () => {
  // Initialize Stripe
  stripe.value = await loadStripe(import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY)

  // Always fetch latest subscription status
  await subscriptionStore.fetchSubscriptionStatus()

  console.log('hasActiveSubscription', subscriptionStore.hasActiveSubscription)
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
        window.history.replaceState({}, document.title, route.path)
      }
    } catch (error) {
      console.error('Failed to verify subscription:', error)
    }
  }
})

const subscriptionPlans = [
  {
    id: 'price_premium',
    name: 'Premium',
    description: 'Unlock all features',
    price: 7,
    features: [
      'Unlimited transactions',
      'Advanced analytics',
      'Export to CSV',
      'Priority support',
      'Multiple users',
      'Team analytics'
    ]
  }
]

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
</script>

<style scoped>
.relative {
  position: relative;
  min-height: 100%;
}

.absolute {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}
</style>
