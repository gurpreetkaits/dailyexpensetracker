<template>
  <PremiumFeatureOverlay description="AI Chat is a premium feature. Subscribe to unlock unlimited access to our AI assistant.">
    <div class="p-6 lg:ml-28 mb-10">
      <div class="bg-white rounded-lg shadow flex flex-col overflow-hidden min-w-full mx-auto min" style="height:75vh;">
        <div class="p-4 border-b border-gray-100 flex flex-col gap-2">
          <h2 class="text-lg font-semibold text-[#10b981]">AI Chat</h2>
        </div>
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3">
          <div v-for="(message, index) in messages" :key="index" :class="message.sender === 'user' ? 'justify-end' : 'justify-start'" class="flex">
            <div :class="message.sender === 'user' ? 'bg-[#10b981] text-white' : 'bg-gray-100 text-gray-800'" class="rounded-2xl px-4 py-2 max-w-[75%] text-sm">
              {{ message.content }}
            </div>
          </div>
          <div v-if="loading" class="flex justify-start">
            <div class="bg-gray-100 text-gray-800 rounded-2xl px-4 py-2 max-w-[75%] text-sm italic opacity-80">
              Reading...
            </div>
          </div>
        </div>
        <div class="bg-white px-4 py-3 border-t border-gray-100 flex items-center gap-2">
          <input v-model="inputMessage" @keyup.enter="sendMessage" :disabled="loading" type="text" placeholder="Type a message..." class="flex-1 rounded-full border border-gray-200 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#10b981] bg-gray-50" />
          <button @click="sendMessage" :disabled="loading" class="w-10 h-10 flex items-center justify-center rounded-full bg-[#10b981] hover:bg-green-600 text-white transition-colors">
            <template v-if="loading">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <rect x="6" y="6" width="12" height="12" rx="2" fill="currentColor"/>
              </svg>
            </template>
            <template v-else>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-7.5-15-7.5v6l10 1.5-10 1.5v6z" />
              </svg>
            </template>
          </button>
        </div>
      </div>
    </div>
  </PremiumFeatureOverlay>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useChatStore } from '../store/chat'
import PremiumFeatureOverlay from '../components/PremiumFeatureOverlay.vue'

const inputMessage = ref('')
const chatStore = useChatStore()
const loading = computed(() => chatStore.loading)
const messages = computed(() => chatStore.messages)

const sendMessage = async () => {
  if (!inputMessage.value.trim() || loading.value) return
  await chatStore.sendMessage({message: inputMessage.value})
  inputMessage.value = ''
}
</script>
