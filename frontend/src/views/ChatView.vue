<template>
  <div class="mx-auto min-w-full">
    <div class="h-[calc(100vh-64px-56px)] md:min-w-full">
      <div class="bg-white h-full md:rounded-lg md:shadow flex flex-col overflow-hidden md:min-w-full md:min-h-[75vh]">
        <!-- Header with assistant name and avatar -->
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <img src="../assets/images/monkey-assistant.png" alt="Dex" class="w-6 h-6" />
            <h2 class="text-lg font-semibold text-gray-900">
              Dex 
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

        <!-- UpgradeModal -->
        <UpgradeModal
          v-model="showUpgradeModal"
          @upgrade="handleUpgrade"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useChatStore } from '../store/chat'
import { useSubscriptionStore } from '../store/subscription'
import { useNotifications } from '../composables/useNotifications'
import { loadStripe } from '@stripe/stripe-js'
import { useAuthStore } from '../store/auth'
import posthog from 'posthog-js'
import { usePolarStore } from '../store/polar'
import UpgradeModal from '../components/UpgradeModal.vue'
import {useRoute} from "vue-router";

const inputMessage = ref('')
const chatStore = useChatStore()
const subscriptionStore = useSubscriptionStore()
const authStore = useAuthStore()
const { notify } = useNotifications()
const loading = computed(() => chatStore.loading)
const messages = computed(() => chatStore.messages)
const hasActiveSubscription = computed(() => polarStore.hasActiveSubscription)
const showUpgradeModal = ref(false)
const stripe = ref(null)
const processingPayment = ref(false)
const polarStore = usePolarStore()

onMounted(async () => {
  try {
    const route = useRoute()
    const checkoutId = route.query?.checkout_id
    if(checkoutId) {
      await polarStore.verifyCheckoutSession(checkoutId)
    } else {
      await polarStore.fetchSubscriptionStatus()
    }

    if (polarStore.hasActiveSubscription) {
      notify({
        title: 'Subscription Active',
        message: 'You are currently on the Pro plan',
        type: 'success'
      })
    } else {
      notify({
        title: 'Basic Plan',
        message: 'Upgrade to Pro for advanced features',
        type: 'info'
      })
    }
  } catch (error) {
    console.error('Subscription status error:', error)
    notify({
      title: 'Error',
      message: error.response?.data?.error || 'Failed to fetch subscription status',
      type: 'error'
    })
  }
})

const handleSendMessage = async () => {
  if (!inputMessage.value.trim() || loading.value) return

  if (!polarStore.hasActiveSubscription) {
    showUpgradeModal.value = true
    notify({
      title: 'Subscription Required',
      message: 'Please upgrade to Pro plan to use chat features',
      type: 'error'
    })
    return
  }

  if (authStore.user?.email) {
    posthog.identify(authStore.user.email, {
      email: authStore.user.email,
      id: authStore.user.id
    })
  }

  try {
    posthog.capture('chat_message_sent', {
      message: inputMessage.value,
      user_id: authStore.user?.id,
      user_email: authStore.user?.email,
      timestamp: new Date().toISOString()
    })

    await sendMessage()
  } catch (error) {
    console.error('Chat error:', error)
    notify({
      title: 'Error',
      message: error.response?.data?.error || 'Failed to send message',
      type: 'error'
    })
  }
}

const sendMessage = async () => {
  try {
    await chatStore.sendMessage({ message: inputMessage.value })
    inputMessage.value = ''
  } catch (error) {
    console.error('Send message error:', error)
    notify({
      title: 'Error',
      message: error.response?.data?.error || 'Failed to process message',
      type: 'error'
    })
    throw error // Re-throw to be caught by handleSendMessage
  }
}

const handleUpgrade = async () => {
  try {
    await polarStore.initiateCheckout()
  } catch (error) {
    console.error('Upgrade failed:', error)
    notify({
      title: 'Upgrade Failed',
      message: error.response?.data?.error || 'Failed to process upgrade',
      type: 'error'
    })
  }
}
</script>
