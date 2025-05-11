<template>
  <PremiumFeatureOverlay :description="description">
    <slot></slot>
  </PremiumFeatureOverlay>
</template>

<script setup>
import { computed } from 'vue'
import { useSubscriptionStore } from '../store/subscription'
import PremiumFeatureOverlay from './PremiumFeatureOverlay.vue'

const props = defineProps({
  description: {
    type: String,
    required: true
  }
})

const subscriptionStore = useSubscriptionStore()
const hasActiveSubscription = computed(() => subscriptionStore.hasActiveSubscription)
</script> 