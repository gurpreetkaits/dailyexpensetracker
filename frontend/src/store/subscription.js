import { defineStore } from 'pinia'
import axios from 'axios'

export const useSubscriptionStore = defineStore('subscription', {
  state: () => ({
    subscription: null,
    loading: false,
    error: null
  }),

  getters: {
    hasActiveSubscription: (state) => {
      if (!state.subscription) return false
      return state.subscription.status === 'active' || state.subscription.status === 'trialing'
    }
  },

  actions: {
    async fetchSubscriptionStatus() {
      this.loading = true
      try {
        const response = await axios.get('/api/subscription/status')
        this.subscription = response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch subscription status'
        console.error('Subscription status error:', error)
      } finally {
        this.loading = false
      }
    },

    async createSubscription(planId) {
      this.loading = true
      try {
        const response = await axios.post('/api/subscription/create', { planId })
        this.subscription = response.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create subscription'
        console.error('Create subscription error:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async cancelSubscription() {
      this.loading = true
      try {
        const response = await axios.post('/api/subscription/cancel')
        this.subscription = response.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to cancel subscription'
        console.error('Cancel subscription error:', error)
        throw error
      } finally {
        this.loading = false
      }
    }
  }
}) 