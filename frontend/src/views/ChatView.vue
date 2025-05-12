<template>
  <div class="mx-auto min-w-full">
    <div class="h-[calc(100vh-64px-56px)] md:min-w-full">
      <div class="bg-white h-full md:rounded-lg md:shadow flex flex-col overflow-hidden md:min-w-full md:min-h-[75vh]">
        <!-- Header with assistant name and avatar -->
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <img src="../assets/images/monkey-assistant.png" alt="Dex" class="w-6 h-6" />
            <h2 class="text-lg font-semibold text-gray-900">
              Dex <span class="text-sm text-[#10b981] font-normal">(beta)</span>
            </h2>
          </div>
        </div>

        <!-- Chat messages area -->
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3 pb-4">
          <!-- Welcome prompt when empty -->
          <div v-if="!loading && messages.length === 0" class="text-gray-500 italic">
            Hi, I’m Dex! Ask me anything about your expenses—like
            <button @click="inputMessage = 'What did I spend on groceries last week?'; sendMessage()"
              class="text-[#10b981] underline sm:block hidden">
              “What did I spend on groceries last week?”
            </button>
          </div>

          <!-- Conversation bubbles -->
          <div v-for="(message, index) in messages" :key="index"
            :class="message.sender === 'user' ? 'justify-end' : 'justify-start'" class="flex">
            <div :class="message.sender === 'user'
                ? 'bg-[#10b981] text-white'
                : 'bg-gray-100 text-gray-800'
              " class="rounded-2xl px-4 py-2 max-w-[75%] text-sm">
              {{ message.content }}
            </div>
          </div>

          <!-- Typing indicator -->
          <div v-if="loading" class="flex justify-start">
            <div class="bg-gray-100 text-gray-800 rounded-2xl px-4 py-2 max-w-[75%] text-sm italic opacity-80">
              Dex is typing...
            </div>
          </div>
        </div>

        <!-- Input area with samples -->
        <div class="bg-white px-4 py-3 border-t border-gray-100 flex flex-col gap-2">
          <div class="flex items-center gap-2">
            <input v-model="inputMessage" @keyup.enter="handleSendMessage" :disabled="loading" type="text"
              placeholder="Ask me to summarize your spending, find a transaction, or set a budget..."
              class="flex-1 rounded-full border border-gray-200 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#10b981] bg-gray-50" />
            <button @click="handleSendMessage" :disabled="loading"
              class="w-10 h-10 flex items-center justify-center rounded-full bg-[#10b981] hover:bg-green-600 text-white transition-colors">
              <template v-if="loading">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                  stroke="currentColor" class="w-5 h-5">
                  <rect x="6" y="6" width="12" height="12" rx="2" fill="currentColor" />
                </svg>
              </template>
              <template v-else>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                  stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-7.5-15-7.5v6l10 1.5-10 1.5v6z" />
                </svg>
              </template>
            </button>
          </div>
          <!-- Sample prompt chips -->
          <div class="flex space-x-2">
            <button @click="inputMessage = 'Show me last month’s spend'; handleSendMessage()"
              class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs hover:bg-gray-200">
              Show last month’s spend
            </button>
            <button @click="inputMessage = 'Find all grocery transactions'; handleSendMessage()"
              class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs hover:bg-gray-200 sm:block hidden">
              Find grocery transactions
            </button>
          </div>
        </div>

        <!-- Subscription Modal -->
        <div v-if="showSubscriptionModal"
          class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
          <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
            <div class="text-center">
              <div class="mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
              </div>
              <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Pro Plan</h2>
                <p class="text-3xl font-bold text-emerald-600 mt-2">$7<span
                    class="text-lg font-normal text-gray-600">/month</span></p>
              </div>

              <div class="space-y-4 mb-6 text-left">
                <div class="flex items-start">
                  <svg class="h-5 w-5 text-emerald-500 mt-0.5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span class="text-gray-700">Custom Categories</span>
                </div>
                <div class="flex items-start">
                  <svg class="h-5 w-5 text-emerald-500 mt-0.5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span class="text-gray-700">Multi Wallet <span class="text-xs text-emerald-600">(coming
                      soon)</span></span>
                </div>
                <div class="flex items-start">
                  <svg class="h-5 w-5 text-emerald-500 mt-0.5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span class="text-gray-700">Weekly Email Updates</span>
                </div>
                <div class="flex items-start">
                  <svg class="h-5 w-5 text-emerald-500 mt-0.5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span class="text-gray-700">AI Chat - Know Yourself Better</span>
                </div>
              </div>

              <div class="flex flex-col gap-3">
                <button @click="handleSubscribe" :disabled="processingPayment"
                  class="w-full bg-emerald-500 text-white px-4 py-3 rounded-lg hover:bg-emerald-600 transition-colors font-medium">
                  {{ processingPayment ? 'Processing...' : 'Upgrade to Pro' }}
                </button>
                <button @click="showSubscriptionModal = false"
                  class="w-full px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors text-sm">
                  Maybe Later
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Script logic remains unchanged
import { ref, computed, onMounted } from 'vue'
import { useChatStore } from '../store/chat'
import { useSubscriptionStore } from '../store/subscription'
import { useNotifications } from '../composables/useNotifications'
import { loadStripe } from '@stripe/stripe-js'

const inputMessage = ref('')
const chatStore = useChatStore()
const subscriptionStore = useSubscriptionStore()
const { notify } = useNotifications()
const loading = computed(() => chatStore.loading)
const messages = computed(() => chatStore.messages)
const hasActiveSubscription = computed(() => subscriptionStore.hasActiveSubscription)
const showSubscriptionModal = ref(false)
const stripe = ref(null)
const processingPayment = ref(false)

onMounted(async () => {
  stripe.value = await loadStripe(import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY)
  await subscriptionStore.fetchSubscriptionStatus()
})


const handleSendMessage = async () => {
  if (!inputMessage.value.trim() || loading.value) return

  // Commenting out premium check to make chat free
  // if (!hasActiveSubscription.value) {
  //   showSubscriptionModal.value = true
  //   return
  // }

  await sendMessage()
}

const sendMessage = async () => {
  await chatStore.sendMessage({ message: inputMessage.value })
  inputMessage.value = ''
}

const handleSubscribe = async () => {
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
      type: 'error',
    })
  } finally {
    processingPayment.value = false
  }
}
</script>
