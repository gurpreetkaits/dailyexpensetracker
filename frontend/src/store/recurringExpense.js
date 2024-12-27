import { defineStore } from "pinia";
import {
  getRecurringExpenses,
  createRecurringExpense,
  updateRecurringExpense,
  deleteRecurringExpense,
} from "../services/RecurringExpense";

export const useRecurringExpenseStore = defineStore("recurringExpense", {
  state: () => ({
    recurringExpenses: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchRecurringExpenses() {
      this.loading = true;
      try {
        const response = await getRecurringExpenses();
        this.recurringExpenses = response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async addRecurringExpense(expense) {
      this.loading = true;
      try {
        const response = await createRecurringExpense(expense);
        this.recurringExpenses.push(response.data);
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateRecurringExpense(expense) {
      this.loading = true;
      try {
        const response = await updateRecurringExpense(expense.id, expense);
        const index = this.recurringExpenses.findIndex(
          (t) => t.id === expense.id
        );
        if (index !== -1) {
          this.recurringExpenses[index] = response.data;
        }
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async removeRecurringExpense(id) {
      this.loading = true;
      try {
        await deleteRecurringExpense(id);
        this.recurringExpenses = this.recurringExpenses.filter(
          (t) => t.id !== id
        );
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});
