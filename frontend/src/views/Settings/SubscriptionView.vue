<template>
  <div class="max-w-7xl mx-auto relative">
    <div class="relative pb-24 px-3 pt-2">
      <div class="bg-white rounded-lg shadow hover:shadow-lg transition-all p-6">
        <div class="pb-4 border-b">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Subscription Status</h2>
            <span class="px-3 py-1 rounded-full text-sm font-medium"
              :class="hasActiveSubscription ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'">
              {{ hasActiveSubscription ? 'Pro Plan Active' : 'No Active Plan' }}
            </span>
          </div>
        </div>
        <div class="pt-6">
          <div v-if="hasActiveSubscription && polarStore.subscription" class="rounded-xl border p-6 bg-blue-50 border-blue-500 ring-1 ring-blue-500">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                  <Crown class="w-5 h-5 text-amber-600" />
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Pro Plan</h3>
                  <p class="text-sm text-gray-600">{{ polarStore.subscription.interval || 'monthly' }} billing</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-2xl font-bold text-gray-900">{{ formatPrice(polarStore.subscription.amount / 100 || 0) }}</p>
                <p class="text-sm text-gray-600">per {{ polarStore.subscription.interval || 'month' }}</p>
              </div>
            </div>

            <div class="mt-6 pt-6 border-t border-blue-200">
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <p class="text-gray-600">Start Date</p>
                  <p class="font-medium text-gray-900">
                    {{ polarStore.subscription.created_at ? new Date(polarStore.subscription.created_at).toLocaleDateString() : '-' }}
                  </p>
                </div>
                <div>
                  <p class="text-gray-600">Next Billing</p>
                  <p class="font-medium text-gray-900">
                    {{ polarStore.subscription.current_period_end ? new Date(polarStore.subscription.current_period_end).toLocaleDateString() : '-' }}
                  </p>
                </div>
              </div>
            </div>

            <div class="mt-6">
              <button @click="handleManageSubscription"
                class="w-full rounded-lg border border-blue-600 px-4 py-2.5 text-sm font-semibold text-blue-600 hover:bg-blue-50 transition-colors">
                Manage Subscription
              </button>
            </div>
          </div>

          <div v-else class="rounded-xl border p-6 bg-gray-50 border-gray-200 text-center">
            <p class="text-gray-600">No active subscription found</p>
            <p class="text-sm text-gray-500 mt-1">Please contact support if you believe this is an error</p>
            <button @click="$router.push({ name: 'plans' })"
              class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
              View Plans
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { usePolarStore } from '../../store/polar'
import { useCurrency } from '../../composables/useCurrency'
import { useNotifications } from '../../composables/useNotifications'
import { Crown } from 'lucide-vue-next'

export default {
  name: 'SubscriptionView',
  components: {
    Crown
  },
  setup() {
    const polarStore = usePolarStore()
    const { currency } = useCurrency()
    const { notify } = useNotifications()
    return { polarStore, currency, notify }
  },
  data() {
    return {
      loading: false
    }
  },
  computed: {
    hasActiveSubscription() {
      return this.polarStore.hasActiveSubscription
    },
    formatPrice() {
      return (price) => {
        return new Intl.NumberFormat(this.currency.value, {
          style: 'currency',
          currency: this.currency.value,
          minimumFractionDigits: this.currency.value === 'INR' ? 0 : 2
        }).format(price)
      }
    }
  },
  methods: {
    handleManageSubscription() {
      this.notify({
        title: 'Coming Soon',
        message: 'Subscription management portal will be available soon',
        type: 'info'
      })
    }
  },
  async created() {
    try {
      this.loading = true
      await this.polarStore.fetchSubscriptionStatus()
    } catch (error) {
      console.error('Error fetching subscription:', error)
      this.notify({
        title: 'Error',
        message: 'Failed to load subscription status',
        type: 'error'
      })
    } finally {
      this.loading = false
    }
  }
}
</script>
