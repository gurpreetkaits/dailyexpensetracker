// src/store/transaction.js
import { defineStore } from "pinia";
import { getChats, sendMessage } from "../services/ChatService";

export const useChatStore = defineStore("chat", {
  state: () => ({
    chats: [],
    messages: [],
  }),
  actions: {
    async fetchChats() {
      const data = await getChats();
      this.chats = data;
    },
    async sendMessage(message) {
      const data = await sendMessage(message);
      this.messages.push(data);
    },
  },
  getters: {
    getChats: (state) => state.chats,
    getMessages: (state) => state.messages,
  },
});
