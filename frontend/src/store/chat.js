// src/store/transaction.js
import { defineStore } from "pinia";
import { getChats, sendMessage } from "../services/ChatService";

export const useChatStore = defineStore("chat", {
  state: () => ({
    chats: [],
    messages: [],
    loading: false,
    conversationId: null,
  }),
  actions: {
    async fetchChats() {
      const data = await getChats();
      this.chats = data;
    },
    async sendMessage({ message }) {
      // 1. Show user message immediately
      this.messages.push({ sender: 'user', content: message });

      this.loading = true;

      try {
        // 3. Send to backend
        const response = await sendMessage({ message, conversation_id: this.conversationId });

        // 4. Save conversationId if returned
        if (response.data.conversation_id) {
          this.conversationId = response.data.conversation_id;
        }

        // 5. Show assistant reply
        this.messages.push({ sender: 'assistant', content: response.data.reply });
      } catch (e) {
        console.error(e);
        this.messages.push({ sender: 'assistant', content: 'Sorry, there was an error.' });
      } finally {
        this.loading = false;
      }
    },
  },
  getters: {
    getChats: (state) => state.chats,
    getMessages: (state) => state.messages,
  },
});
