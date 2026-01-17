<template>
  <div class="max-w-7xl mx-auto relative">
    <div class="relative pb-24 px-3 pt-2">
      <div class="flex flex-col bg-white rounded-xl shadow-sm overflow-hidden" style="height: calc(100vh - 180px);">

    <!-- Header -->
    <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 flex-shrink-0">
      <div class="w-9 h-9 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center">
        <Sparkles class="w-4 h-4 text-white" />
      </div>
      <div>
        <h2 class="text-sm font-semibold text-gray-900">Dex AI</h2>
        <p class="text-xs text-gray-400">Finance Assistant</p>
      </div>
    </div>

    <!-- Chat Area -->
    <div class="flex-1 flex flex-col items-center justify-center px-4 overflow-hidden">

      <!-- Empty State -->
      <div v-if="!loading && messages.length === 0" class="text-center">
        <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mb-4 mx-auto">
          <Sparkles class="w-7 h-7 text-gray-300" />
        </div>
        <p class="text-gray-900 font-medium mb-1">How can I help?</p>
        <p class="text-xs text-gray-400 mb-5">Ask about your spending</p>

        <div class="flex flex-wrap justify-center gap-2">
          <button
            v-for="prompt in quickPrompts"
            :key="prompt"
            @click="inputMessage = prompt; handleSendMessage()"
            class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 rounded-full text-xs text-gray-500 transition-colors"
          >
            {{ prompt }}
          </button>
        </div>
      </div>

      <!-- Messages -->
      <div v-else class="w-full overflow-y-auto space-y-3 py-4">
        <div v-for="(message, index) in messages" :key="index">
          <!-- User -->
          <div v-if="message.sender === 'user'" class="flex justify-end">
            <div class="bg-emerald-500 text-white rounded-2xl rounded-br-sm px-4 py-2 max-w-[80%]">
              <p class="text-sm">{{ message.content }}</p>
            </div>
          </div>
          <!-- Assistant -->
          <div v-else class="flex gap-2">
            <div class="w-6 h-6 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center flex-shrink-0">
              <Sparkles class="w-3 h-3 text-white" />
            </div>
            <div class="bg-gray-100 rounded-2xl rounded-tl-sm px-4 py-2 max-w-[80%]">
              <p class="text-sm text-gray-800">{{ message.content }}</p>
            </div>
          </div>
        </div>

        <!-- Typing -->
        <div v-if="loading" class="flex gap-2">
          <div class="w-6 h-6 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <Sparkles class="w-3 h-3 text-white" />
          </div>
          <div class="bg-gray-100 rounded-2xl rounded-tl-sm px-4 py-2.5">
            <div class="flex gap-1">
              <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
              <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
              <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Input -->
    <div class="px-4 py-3 border-t border-gray-100 flex-shrink-0">
      <div class="flex items-center gap-2">
        <input
          v-model="inputMessage"
          @keyup.enter="handleSendMessage"
          :disabled="loading"
          type="text"
          placeholder="Ask something..."
          class="flex-1 bg-gray-50 rounded-full px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 placeholder-gray-400"
        />
        <button
          @click="handleSendMessage"
          :disabled="loading || !inputMessage.trim()"
          class="w-10 h-10 flex items-center justify-center rounded-full bg-emerald-500 text-white hover:bg-emerald-600 disabled:opacity-40 transition-colors flex-shrink-0"
        >
          <Send v-if="!loading" class="w-4 h-4" />
          <Loader2 v-else class="w-4 h-4 animate-spin" />
        </button>
      </div>
    </div>

    <!-- Upgrade Popup -->
    <Transition name="fade">
      <div v-if="showUpgradePopup" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click="showUpgradePopup = false">
        <div class="bg-white rounded-2xl p-5 mx-4 max-w-xs text-center" @click.stop>
          <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mx-auto mb-3">
            <Crown class="w-6 h-6 text-emerald-600" />
          </div>
          <h3 class="font-semibold text-gray-900 mb-1">Pro Feature</h3>
          <p class="text-sm text-gray-500 mb-4">Upgrade to use AI chat</p>
          <router-link
            to="/plans"
            class="block w-full py-2.5 bg-emerald-500 text-white text-sm font-medium rounded-xl hover:bg-emerald-600 transition-colors"
          >
            Upgrade
          </router-link>
          <button @click="showUpgradePopup = false" class="mt-2 text-xs text-gray-400">
            Later
          </button>
        </div>
      </div>
    </Transition>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useChatStore } from '../store/chat'
import { useNotifications } from '../composables/useNotifications'
import { usePolarStore } from '../store/polar'
import { useRoute } from "vue-router"
import { Sparkles, Crown, Send, Loader2 } from 'lucide-vue-next'

const inputMessage = ref('')
const chatStore = useChatStore()
const { notify } = useNotifications()
const loading = computed(() => chatStore.loading)
const messages = computed(() => chatStore.messages)
const polarStore = usePolarStore()
const showUpgradePopup = ref(false)

const quickPrompts = [
  "Last month's spending",
  "Top categories",
  "This week's expenses",
  "Compare to last month"
]

onMounted(async () => {
  try {
    const route = useRoute()
    const checkoutId = route.query?.checkout_id
    if (checkoutId) {
      await polarStore.verifyCheckoutSession(checkoutId)
      if (polarStore.hasActiveSubscription) {
        notify({ title: 'Welcome to Pro!', message: 'Subscription activated', type: 'success' })
      }
    } else {
      await polarStore.fetchSubscriptionStatus()
    }
  } catch (error) {
    console.error('Subscription error:', error)
  }
})

const handleSendMessage = async () => {
  if (!inputMessage.value.trim() || loading.value) return

  if (!polarStore.hasActiveSubscription) {
    showUpgradePopup.value = true
    return
  }

  try {
    inputMessage.value = ''
    notify({ title: 'Coming Soon', message: 'AI Chat is temporarily disabled', type: 'info' })
  } catch (error) {
    notify({ title: 'Error', message: 'Failed to send message', type: 'error' })
  }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
