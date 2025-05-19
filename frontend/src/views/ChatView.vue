<template>
  <div class="mx-auto min-w-full">
    <div class="h-[calc(100vh-64px-56px)] md:min-w-full">
      <div class="bg-white h-full md:rounded-lg md:shadow flex flex-col overflow-hidden md:min-w-full md:min-h-[75vh]">
        <!-- Header with assistant name and avatar -->
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <img src="../assets/images/monkey-assistant.png" alt="Dex" class="w-6 h-6" />
            <h2 class="text-lg font-semibold text-gray-900 gap-1">
              Dex <span class=" bg-blue-100 text-blue-600 text-[10px] px-1.5 py-0.5 rounded-full">PRO</span>
            </h2>
          </div>
        </div>

        <!-- Chat messages area -->
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3 pb-4">
          <!-- Welcome prompt when empty -->
          <div v-if="!loading && messages.length === 0" class="text-gray-500 italic">
            Hi, I'm Dex! Ask me anything about your expenses—like
            <button @click="inputMessage = 'What did I spend on groceries last week?'; sendMessage()"
              class="text-[#10b981] underline sm:block hidden">
              "What did I spend on groceries last week?"
            </button>
          </div>

          <!-- Conversation bubbles -->
          <div v-for="(message, index) in messages" :key="index"
            :class="message.sender === 'user' ? 'justify-end' : 'justify-start'" class="flex flex-col">
            <div :class="message.sender === 'user'
                ? 'bg-[#10b981] text-white self-end max-w-[75%]'
                : 'bg-gray-100 text-gray-800 max-w-[75%]'"
              class="rounded-2xl px-4 py-2 text-sm mb-1">
              {{ message.content }}
            </div>
            <!-- Transaction Table -->
            <div v-if="message.sender === 'assistant' && message.transactions?.length" class="mt-1 max-w-[420px] text-xs self-start">
              <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
                <table class="min-w-full divide-y divide-gray-100">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-2 py-1.5 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">Date</th>
                      <th class="px-2 py-1.5 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">Category</th>
                      <th class="px-2 py-1.5 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider w-[80px] max-w-[80px]">Note</th>
                      <th class="px-2 py-1.5 text-right text-[10px] font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-100">
                    <tr v-for="transaction in message.transactions" :key="transaction.id" class="hover:bg-gray-50">
                      <td class="px-2 py-1.5 text-[11px] text-gray-900 whitespace-nowrap">{{ formatDate(transaction.transaction_date) }}</td>
                      <td class="px-2 py-1.5 text-[11px] text-gray-900 whitespace-nowrap">{{ transaction.category }}</td>
                      <td class="px-2 py-1.5 text-[11px] text-gray-900 truncate max-w-[80px] relative"
                          @mouseenter="showTooltip(transaction.id, transaction.note)"
                          @mouseleave="hideTooltip">
                        {{ transaction.note }}
                        <div v-if="hoveredNoteId === transaction.id" class="absolute z-10 left-1/2 -translate-x-1/2 bottom-full mb-1 w-max max-w-xs bg-gray-900 text-white text-xs rounded px-2 py-1 shadow-lg pointer-events-none whitespace-pre-line">
                          {{ hoveredNoteText }}
                        </div>
                      </td>
                      <td class="px-2 py-1.5 text-[11px] text-right whitespace-nowrap" :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                        {{ formatAmount(transaction.amount, transaction.type) }}
                      </td>
                    </tr>
                  </tbody>
                  <tfoot v-if="message.summary" class="bg-gray-50 text-[11px]">
                    <tr>
                      <td colspan="3" class="px-2 py-1.5 font-medium text-gray-900">Total Income</td>
                      <td class="px-2 py-1.5 text-right text-green-600 whitespace-nowrap">{{ formatAmount(message.summary.total_income) }}</td>
                    </tr>
                    <tr>
                      <td colspan="3" class="px-2 py-1.5 font-medium text-gray-900">Total Expense</td>
                      <td class="px-2 py-1.5 text-right text-red-600 whitespace-nowrap">{{ formatAmount(message.summary.total_expense) }}</td>
                    </tr>
                    <tr>
                      <td colspan="3" class="px-2 py-1.5 font-medium text-gray-900">Balance</td>
                      <td class="px-2 py-1.5 text-right whitespace-nowrap" :class="message.summary.balance >= 0 ? 'text-green-600' : 'text-red-600'">
                        {{ formatAmount(message.summary.balance) }}
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
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
            <button @click="inputMessage = 'Show me last month\'s spend'; handleSendMessage()"
              class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs hover:bg-gray-200">
              Show last month's spend
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
import { format } from 'date-fns'
import { Sparkle } from 'lucide-vue-next'

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
const hoveredNoteId = ref(null)
const hoveredNoteText = ref('')

onMounted(async () => {
  try {
    const route = useRoute()
    const checkoutId = route.query?.checkout_id
    if(checkoutId) {
      await polarStore.verifyCheckoutSession(checkoutId)
      console.log('polarStore.hasActiveSubscription', polarStore.hasActiveSubscription)
      if (polarStore.hasActiveSubscription) {
        notify({
          title: 'Subscription Activated',
          message: 'Welcome to the Pro plan!',
          type: 'success'
        })
      }
    } else {
      // Only fetch if needed (will use cached data if recent)
      await polarStore.fetchSubscriptionStatus()
      if (!hasActiveSubscription.value) {
        // notify({
        //   title: 'Basic Plan',
        //   message: 'Upgrade to Pro for advanced features',
        //   type: 'info'
        // })
      }
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

const formatDate = (dateString) => {
  return format(new Date(dateString), 'MMM d, yyyy')
}

const formatAmount = (amount, type = null) => {
  const formattedAmount = new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(Math.abs(amount))

  if (type === 'income') {
    return `+${formattedAmount}`
  } else if (type === 'expense') {
    return `-${formattedAmount}`
  }
  return formattedAmount
}

const showTooltip = (id, note) => {
  hoveredNoteId.value = id
  hoveredNoteText.value = note
}
const hideTooltip = () => {
  hoveredNoteId.value = null
  hoveredNoteText.value = ''
}
</script>
