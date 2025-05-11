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
          <button @click="handleUpgrade"
                  class="w-full bg-emerald-500 text-white px-6 py-3 rounded-lg hover:bg-emerald-600 transition-colors">
            View Plans & Upgrade
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
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useSubscriptionStore } from '../store/subscription'

const props = defineProps({
  description: {
    type: String,
    default: 'This is a premium feature. Subscribe to unlock unlimited access.'
  }
})

const router = useRouter()
const subscriptionStore = useSubscriptionStore()
const hasActiveSubscription = computed(() => subscriptionStore.hasActiveSubscription)

onMounted(async () => {
  await subscriptionStore.fetchSubscriptionStatus()
})

const handleUpgrade = () => {
  router.push('/settings/plans')
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
