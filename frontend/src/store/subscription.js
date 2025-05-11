import { defineStore } from 'pinia'
import {
  createSubscription,
  getSubscriptionStatus,
  cancelSubscription,
  resumeSubscription,
  updatePaymentMethod,
  verifyCheckoutSession
} from '../services/SubscriptionService'
import { useNotifications } from '../composables/useNotifications'

export const useSubscriptionStore = defineStore('subscription', {
  state: () => ({
    subscription: null,
    loading: false,
    error: null,
    sessionVerified: false
  }),

  getters: {
    hasActiveSubscription: (state) => {
      if (!state.subscription) return false
      return ['active', 'trialing'].includes(state.subscription.stripe_status)
    },

    subscriptionStatus: (state) => {
      return state.subscription?.status
    },

    defaultPaymentMethod: (state) => {
      return state.subscription?.defaultPaymentMethod
    },

    isSessionVerified: (state) => {
      return state.sessionVerified
    }
  },

  actions: {
    async verifyCheckoutSession(sessionId) {
      if (!sessionId) return false

      this.loading = true
      this.error = null
      const { notify } = useNotifications()

      try {
        const response = await verifyCheckoutSession(sessionId)
        if (response.success) {
          this.subscription = response?.subscription
          this.sessionVerified = true
          notify({
            title: 'Success',
            message: 'Your subscription has been activated successfully!',
            type: 'success'
          })
          return true
        } else {
          throw new Error(response.data.message)
        }
      } catch (error) {
        this.error = error.message
        notify({
          title: 'Error',
          message: error.message || 'Failed to verify subscription status',
          type: 'error'
        })
        return false
      } finally {
        this.loading = false
      }
    },

    async fetchSubscriptionStatus() {
      this.loading = true
      this.error = null

      try {
        const response = await getSubscriptionStatus()
        this.subscription = response.subscription
        this.sessionVerified = true
      } catch (error) {
        this.error = error.message

      } finally {
        this.loading = false
      }
    },

    async createSubscription() {
      this.loading = true
      this.error = null

      try {
        const response = await createSubscription()
        console.log('Subscription response:', response)
        return response
      } catch (error) {
        this.error = error.message
        const { notify } = useNotifications()
        notify({
          title: 'Error',
          message: error.message || 'Failed to create subscription',
          type: 'error'
        })
        throw error
      } finally {
        this.loading = false
      }
    },

    async cancelSubscription() {
      this.loading = true
      this.error = null
      const { notify } = useNotifications()

      try {
        await cancelSubscription()
        await this.fetchSubscriptionStatus()
        notify({
          title: 'Success',
          message: 'Your subscription has been canceled',
          type: 'success'
        })
      } catch (error) {
        this.error = error.message
        notify({
          title: 'Error',
          message: error.message || 'Failed to cancel subscription',
          type: 'error'
        })
        throw error
      } finally {
        this.loading = false
      }
    },

    async resumeSubscription() {
      this.loading = true
      this.error = null
      const { notify } = useNotifications()

      try {
        await resumeSubscription()
        await this.fetchSubscriptionStatus()
        notify({
          title: 'Success',
          message: 'Your subscription has been resumed',
          type: 'success'
        })
      } catch (error) {
        this.error = error.message
        notify({
          title: 'Error',
          message: error.message || 'Failed to resume subscription',
          type: 'error'
        })
        throw error
      } finally {
        this.loading = false
      }
    },

    async updatePaymentMethod(paymentMethodId) {
      this.loading = true
      this.error = null
      const { notify } = useNotifications()

      try {
        await updatePaymentMethod(paymentMethodId)
        await this.fetchSubscriptionStatus()
        notify({
          title: 'Success',
          message: 'Payment method updated successfully',
          type: 'success'
        })
      } catch (error) {
        this.error = error.message
        notify({
          title: 'Error',
          message: error.message || 'Failed to update payment method',
          type: 'error'
        })
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})
