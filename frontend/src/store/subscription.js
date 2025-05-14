import { defineStore } from 'pinia'
import {
  createSubscription,
  getSubscriptionStatus,
  cancelSubscription,
  resumeSubscription,
  verifyCheckoutSession,
  getSubscriptionHistory
} from '../services/SubscriptionService'
import { useNotifications } from '../composables/useNotifications'

export const useSubscriptionStore = defineStore('subscription', {
  state: () => ({
    subscription: null,
    loading: false,
    error: null,
    sessionVerified: false,
    subscriptionHistory: [],
    pagination: {
      current_page: 1,
      per_page: 10,
      total: 0,
      last_page: 1,
      from: 1,
      to: 1
    }
  }),

  getters: {
    hasActiveSubscription: (state) => {
      if (!state.subscription) return false
      return state.subscription.status === 'active'
    },

    subscriptionStatus: (state) => {
      return state.subscription?.status
    },

    isSessionVerified: (state) => {
      return state.sessionVerified
    }
  },

  actions: {
    async verifyCheckoutSession(checkoutId) {
      if (!checkoutId) return false

      this.loading = true
      this.error = null
      const { notify } = useNotifications()

      try {
        const response = await verifyCheckoutSession(checkoutId)
        if (response.success) {
          this.subscription = response.subscription
          this.sessionVerified = true
          notify({
            title: 'Success',
            message: 'Your subscription has been activated successfully!',
            type: 'success'
          })
          return true
        } else {
          throw new Error(response.message)
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

    async fetchSubscriptionHistory(page = 1) {
      this.loading = true;
      try {
        const response = await getSubscriptionHistory(page);
        this.subscriptionHistory = response.data;
        this.pagination = {
          current_page: response.current_page,
          per_page: response.per_page,
          total: response.total,
          last_page: response.last_page,
          from: response.from,
          to: response.to
        };
        return response.data;
      } catch (error) {
        this.error = error.message;
        console.error('Error fetching subscription history:', error);
        return null;
      } finally {
        this.loading = false;
      }
    }
  }
})
