// src/store/settings.js
import { defineStore } from "pinia";
import {
  saveSettings,
  getSettings,
  getStats,
} from "../services/SettingsService";
import { useAuthStore } from "./auth";

export const useSettingsStore = defineStore("settings", {
  state: () => ({
    currency: "",
    currencyCode: "",
    currencySymbol: "",
    billReminders: false,
    isLoaded: false,
    categories: [],
    stats: [],
    userIncome: 0,
  }),

  actions: {
    async initializeSettings() {
      if (this.isLoaded) return; // Don't load if already initialized
      // Load from localStorage
      const savedSettings = localStorage.getItem("expense-tracker-settings");
      if (savedSettings) {
        const settings = JSON.parse(savedSettings);
        this.currency = settings.currency.code;
        this.billReminders = settings.billReminders;
        this.userIncome = settings.income;
      }
      this.isLoaded = true;
    },

    async updateSettings(settings) {
      // Update store
      this.currency = settings.currency;
      this.billReminders = settings.billReminders;
      this.userIncome = settings.income;

      localStorage.setItem(
        "expense-tracker-settings",
        JSON.stringify({
          currency: this.currency,
          billReminders: this.billReminders,
        })
      );
    },

    async fetchSettings() {
      if (this.isLoaded) return; // Don't fetch if already loaded

      this.loading = true;
      try {
        // First try to get from cache
        const cached = localStorage.getItem("app-settings");
        if (cached) {
          const settings = JSON.parse(cached);
          this.updateStateFromSettings(settings);
          return;
        }

        // If no cache, fetch from API
        const { data } = await getSettings();
        if (data && data[0]) {
          this.updateStateFromSettings(data[0]);

          // Cache the settings
          localStorage.setItem("app-settings", JSON.stringify(data[0]));
        }
      } catch (error) {
        console.error("Error fetching settings:", error);
      } finally {
        this.loading = false;
      }
    },

    updateStateFromSettings(settings) {
      this.currency = settings.currency?.code || "";
      this.currencyCode = settings.currency?.code || "";
      this.currencySymbol = settings.currency?.symbol || "₹";
      this.billReminders = settings.reminders || false;
      this.userIncome = settings.income || 0;
      this.isLoaded = true;
    },

    clearCache() {
      localStorage.removeItem("app-settings");
      this.isLoaded = false;
    },

    async addSettings(settings) {
      try {
        this.clearCache();
        await saveSettings(settings);
        await this.fetchSettings();
      } catch (error) {
        console.error(error);
      }
    },

    async fetchCategories() {
      if (this.isLoaded) return;

      this.loading = true;
      try {
        const { data } = await this.fetchCategories();
        this.categories = data;
        return data;
      } catch (error) {
        console.error("Error fetching settings:", error);
      } finally {
        this.loading = false;
      }
    },

    async isGurpreet() {
      let authStore = useAuthStore()
      return (
        (await authStore.getAuth().email) === "gurpreetkait.codes@gmail.com"
      );
    },
    async loadStats(page = 1) {
      if (!this.isGurpreet()) return;
      const { data } = await getStats(page);
      this.stats = data;
      return data;
    },
  },
});
