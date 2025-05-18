import { defineStore } from 'pinia';
import { cancelSubscription, getSubscriptionStatus, getCheckoutUrl, verifyCheckoutSession } from '../services/PolarService';
import { useAuthStore } from './auth';

export const usePolarStore = defineStore('polar', {
    state: () => ({
        subscription: null,
        subscriptionStatus: null,
        loading: false,
        error: null,
        lastFetched: null,
    }),

    getters: {
        hasActiveSubscription: (state) => {
            return state.subscriptionStatus === 'active';
        },
        isOnTrial: (state) => {
            return state.subscription?.trial_ends_at || false;
        },
        subscriptionPlan: (state) => {
            return state.subscription?.plan_type || null;
        },
        shouldRefetchSubscription: (state) => {
            if (!state.lastFetched) return true;
            const fiveMinutes = 5 * 60 * 1000;
            return Date.now() - state.lastFetched > fiveMinutes;
        }
    },

    actions: {
        async verifyCheckoutSession(checkoutId) {
            const response = await verifyCheckoutSession(checkoutId);
            console.log('verifyCheckoutSession', response);
            if(response.success) {
                this.subscription = response.subscription;
                this.subscriptionStatus = response.subscription.status;
            } else {
                this.error = response.message;
            }
        },
        async fetchSubscriptionStatus(force = false) {
            const authStore = useAuthStore();
            if (!authStore.isAuthenticated) {
                this.subscription = null;
                return;
            }

            if (!force && !this.shouldRefetchSubscription) {
                return;
            }

            this.loading = true;
            this.error = null;

            try {
                const response = await getSubscriptionStatus();
                console.log('fetching subscription status', response);
                if (response.subscription) {
                    this.subscriptionStatus = response.subscription?.status;
                    this.subscription = response?.subscription;
                    this.lastFetched = Date.now();
                } else {
                    this.error = response.message;
                }
            } catch (error) {
                this.error = error.message || 'Failed to fetch subscription status';
                console.error('Subscription status fetch error:', error);
            } finally {
                this.loading = false;
            }
        },

        async initiateCheckout(type) {
            const checkoutUrl = getCheckoutUrl(type);
            if (!checkoutUrl) {
                throw new Error('Checkout URL not configured');
            }
            window.location.href = checkoutUrl;
        },

        async cancelSubscription() {
            this.loading = true;
            this.error = null;

            try {
                const response = await cancelSubscription();
                if (response.success) {
                    await this.fetchSubscriptionStatus();
                    return true;
                } else {
                    this.error = response.message;
                    throw new Error(response.message);
                }
            } catch (error) {
                this.error = error.message || 'Failed to cancel subscription';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        clearSubscription() {
            this.subscription = null;
            this.subscriptionStatus = null;
            this.error = null;
            this.lastFetched = null;
        },
    },
});
