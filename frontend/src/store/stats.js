// src/store/settings.js
import { defineStore } from "pinia";
import { getYearlyComparison } from "../services/StatsService";


export const useStatsStore = defineStore("stats", {
  state: () => ({
    currentYearData: [],
    previousYearData: [],
    yearlyComparison: [],
  }),
  actions: {
    async getYearlyComparison(previousYear, currentYear) {
      const data = await getYearlyComparison(previousYear, currentYear);
      this.yearlyComparison = data;
      this.currentYearData = data.current_year;
      this.previousYearData = data.previous_year;
    },
  },
});