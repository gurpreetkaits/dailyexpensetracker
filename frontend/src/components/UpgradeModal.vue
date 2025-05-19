<template>
    <TransitionRoot appear :show="modelValue" as="template">
      <Dialog as="div" @close="$emit('update:modelValue', false)" class="relative z-50">
        <TransitionChild
          enter="duration-200 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all">
                <div class="text-center">
                  <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                    <Sparkle class="h-6 w-6 text-blue-600" />
                  </div>
                  <DialogTitle as="h3" class="mt-4 text-lg font-semibold leading-6 text-gray-900">
                    Upgrade to Plus
                  </DialogTitle>
                  <div class="mt-2">
                    <p class="text-sm  text-red-600">
                      For less than the cost of a pizza, get powerful tools to control your expenses and understand your spendings!
                    </p>
                  </div>
                </div>

                <div class="mt-6 space-y-4">
                  <div class="rounded-lg bg-blue-50 p-4">
                    <div class="flex items-center justify-between">
                      <div>
                        <h4 class="text-base font-semibold text-blue-900">Pro Plan</h4>
                        <div class="flex items-center gap-2">
                          <p class="text-sm text-blue-700">$2.99/month</p>
                          <span class="text-xs text-blue-600">or</span>
                          <p class="text-sm text-blue-700">$29.9/year</p>
                          <span class="text-xs bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded-full">Save 17%</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="space-y-3">
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">Track daily expenses with ease</p>
                    </div>
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">Real-time spending overview</p>
                    </div>
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">Unlimited Category</p>
                    </div>
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">Unlimited Wallets</p>
                    </div>
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">AI-powered spending insights</p>
                    </div>
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">Weekly & monthly reports</p>
                    </div>
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">Reminders (coming soon) and Sync With Calendar</p>
                    </div>
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">Cloud Backup (coming soon)</p>
                    </div>
                  </div>

                  <div class="mt-6 flex flex-col gap-3">
                    <div class="grid grid-cols-2 gap-3">
                      <button
                        @click="handleUpgrade('monthly')"
                        :disabled="isLoading"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                      >
                        <span v-if="isLoading">
                          <Loader class="h-5 w-5 text-white animate-spin" />
                        </span>
                        <span v-else>Monthly Plan</span>
                      </button>
                      <button
                        @click="handleUpgrade('yearly')"
                        :disabled="isLoading"
                        class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                      >
                        <span v-if="isLoading">
                          <Loader class="h-5 w-5 text-white animate-spin" />
                        </span>
                        <span v-else>Yearly Plan</span>
                      </button>
                    </div>
                    <!-- <button
                      v-if="!isRequired"
                      @click="$emit('update:modelValue', false)"
                      class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                    >
                      Maybe Later
                    </button> -->
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </template>

  <script>
  import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
  import { Sparkle, CheckCircle, Loader } from 'lucide-vue-next'
  import { usePolarStore } from '../store/polar'
  import { useRouter, useRoute } from 'vue-router'
  import { mapState, mapActions } from 'pinia'
  import { useNotifications } from '../composables/useNotifications'
  import { ref, onMounted, watch } from 'vue'
  import { useSettingsStore } from '../store/settings'
  
  export default {
    name: 'UpgradeModal',
    components: {
      Dialog,
      DialogPanel,
      DialogTitle,
      TransitionRoot,
      TransitionChild,
      Sparkle,
      CheckCircle,
      Loader
    },
    props: {
      modelValue: {
        type: Boolean,
        required: true
      },
      isRequired: {
        type: Boolean,
        default: false
      }
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
      const polarStore = usePolarStore()
      const router = useRouter()
      const route = useRoute()
      const { notify } = useNotifications()
      const isLoading = ref(false)
      const error = ref(null)

      const verifyCheckout = async (checkoutId) => {
        if (!checkoutId) return
        
        console.log('Verifying checkout:', checkoutId) // Debug log
        isLoading.value = true
        try {
          const result = await polarStore.verifyCheckoutSession(checkoutId)
          console.log('Verification result:', result) // Debug log
          
          if (polarStore.hasActiveSubscription) {
            notify({
              title: 'Activated',
              message: 'Welcome to the Pro plan!',
              type: 'success'
            })
            emit('update:modelValue', false) // Close modal
            router.push('/')
          } else {
            notify({
              title: 'Subscription Failed',
              message: 'Failed to activate subscription. Please try again.',
              type: 'error'
            })
          }
        } catch (error) {
          console.error('Checkout verification error:', error)
          notify({
            title: 'Error',
            message: error.response?.data?.error || 'Failed to verify subscription',
            type: 'error'
          })
        } finally {
          isLoading.value = false
        }
      }

      // Watch for route changes to handle checkout_id
      watch(() => route.query.checkout_id, async (newCheckoutId) => {
        localStorage.setItem('pending_checkout_id', newCheckoutId)
        if (newCheckoutId) {
          console.log('Checkout ID detected in URL:', newCheckoutId) // Debug log
          await verifyCheckout(newCheckoutId)
        }
      }, { immediate: true })

      // Also check on mount
      onMounted(async () => {
        if(route.query.checkout_id){
          localStorage.setItem('pending_checkout_id', route.query.checkout_id)
        }
        const checkoutId = route.query.checkout_id
        if (checkoutId) {
          console.log('Initial checkout ID on mount:', checkoutId) // Debug log
          await verifyCheckout(checkoutId)
        }
      })

      return { 
        polarStore, 
        router,
        isLoading,
        error,
        verifyCheckout
      }
    },
    computed: {
      ...mapState(usePolarStore, ['hasActiveSubscription'])
    },
    methods: {
      ...mapActions(usePolarStore, ['initiateCheckout']),
       handleUpgrade(plan) {
        this.isLoading = true
        try {
          this.initiateCheckout(plan)
        } catch (error) {
          console.error('Failed to create checkout:', error)
          notify({
            title: 'Upgrade Failed',
            message: error.response?.data?.error || 'Failed to start subscription process',
            type: 'error'
          })
        } finally {
          this.isLoading = false
        }
      }
    }
  }
  </script>

