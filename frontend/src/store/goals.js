// src/store/goal.js
import { defineStore } from "pinia";
import { useTransactionStore } from "./transaction";
import {
  getGoals,
  getGoalById,
  createGoal,
  updateGoal,
  deleteGoal,
} from "../services/GoalService";
import { useSettingsStore } from "./settings";

export const useGoalStore = defineStore("goals", {
  state: () => ({
    goals: [],
    loading: false,
    error: null,
    currentGoal: null,
  }),

  getters: {
    totalBalance() {
      const transactionStore = useTransactionStore();
      return transactionStore.balance;
    },

    totalAllocatedToGoals() {
      return this.goals.reduce((sum, goal) => {
        const saved = parseFloat(goal.saved || 0);
        return sum + saved;
      }, 0);
    },

    remainingToAllocate() {
      return Math.max(this.totalBalance - this.totalAllocatedToGoals, 0);
    },

    unfulfilledGoals() {
      return this.goals
        .filter((goal) => {
          const saved = parseFloat(goal.saved || 0);
          const target = parseFloat(goal.target || 0);
          return saved < target;
        })
        .sort((a, b) => new Date(a.target_date) - new Date(b.target_date));
    },

    sortedGoals() {
      return [...this.goals].sort((a, b) => {
        const progressA =
          (parseFloat(a.saved || 0) / parseFloat(a.target || 1)) * 100;
        const progressB =
          (parseFloat(b.saved || 0) / parseFloat(b.target || 1)) * 100;
        return progressB - progressA;
      });
    },

    goalsProgress() {
      return this.goals.map((goal) => ({
        ...goal,
        progress:
          (parseFloat(goal.saved || 0) / parseFloat(goal.target || 1)) * 100,
        remaining: parseFloat(goal.target || 0) - parseFloat(goal.saved || 0),
      }));
    },
  },

  actions: {
    async fetchGoals() {
      this.loading = true;
      try {
        const response = await getGoals();
        this.goals = response.data.data;
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to fetch goals";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchGoal(id) {
      this.loading = true;
      try {
        const response = await getGoalById(id);
        this.currentGoal = response.data.data;
        return this.currentGoal;
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to fetch goal";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async addGoal(goalData) {
      this.loading = true;
      try {
        goalData.saved = parseFloat(goalData.saved || 0);
        const response = await createGoal(goalData);
        const newGoal = response.data.data;
        this.goals.push(newGoal);
        return newGoal;
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to add goal";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateGoal({ id, ...goalData }) {
      this.loading = true;
      try {
        const response = await updateGoal(id, goalData);
        const updatedGoal = response.data.data;
        const index = this.goals.findIndex((g) => g.id === id);
        if (index !== -1) {
          this.goals[index] = updatedGoal;
        }
        return updatedGoal;
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to update goal";
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async removeGoal(id) {
      this.loading = true;
      try {
        await deleteGoal(id);
        this.goals = this.goals.filter((goal) => goal.id !== id);
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to delete goal";
        throw error;
      } finally {
        this.loading = false;
      }
    }
  },
});