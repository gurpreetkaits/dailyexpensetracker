<template>
  <div>
    <slot></slot>
    <UpgradeModal
      v-model="showUpgradeModal"
      :is-required="true"
    />
  </div>
</template>

<script>
import { computed, ref, onMounted } from 'vue'
import { usePolarStore } from '../store/polar'
import UpgradeModal from './UpgradeModal.vue'
import { useRouter } from 'vue-router'

export default {
  name: 'SubscriptionGuard',
  components: {
    UpgradeModal
  },
  setup() {
    const polarStore = usePolarStore()
    const router = useRouter()
    const showUpgradeModal = ref(false)

    const hasActiveSubscription = computed(() => polarStore.hasActiveSubscription)

    onMounted(async () => {
      try {
        await polarStore.fetchSubscriptionStatus()
        if (!hasActiveSubscription.value) {
          showUpgradeModal.value = true
        }
      } catch (error) {
        console.error('Failed to fetch subscription status:', error)
      }
    })

    return {
      hasActiveSubscription,
      showUpgradeModal
    }
  }
}
</script> 