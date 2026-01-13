import { defineStore } from "pinia";
import {
  getRecurringExpenses,
  createRecurringExpense,
  updateRecurringExpense,
  deleteRecurringExpense,
  getRecurringSuggestions,
} from "../services/RecurringExpense";

export const useRecurringExpenseStore = defineStore("recurringExpense", {
  state: () => ({
    recurringExpenses: [],
    grouped: {
      subscriptions: [],
      emis: [],
      bills: [],
      other: []
    },
    suggestions: [],
    loading: false,
    loadingSuggestions: false,
    error: null,
    summary: {
      monthly_payment_total: 0,
      total_amount_paid: 0,
      total_interest_paid: 0,
      total_pending_payments: 0,
      total_remaining_balance: 0,
      total_yearly_cost: 0,
      upcoming_payments: []
    }
  }),

  getters: {
    totalYearlyCost: (state) => {
      return state.recurringExpenses
        .filter(e => e.is_active)
        .reduce((sum, e) => sum + (e.yearly_cost || 0), 0);
    },
    activeExpensesCount: (state) => {
      return state.recurringExpenses.filter(e => e.is_active).length;
    }
  },

  actions: {
    async fetchRecurringExpenses() {
      this.loading = true;
      try {
        const response = await getRecurringExpenses();
        this.recurringExpenses = response.data.recurring_expenses;
        this.summary = response.data.total;
        this.grouped = response.data.grouped || {
          subscriptions: [],
          emis: [],
          bills: [],
          other: []
        };
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchSuggestions() {
      this.loadingSuggestions = true;
      try {
        const response = await getRecurringSuggestions();
        this.suggestions = response.data.suggestions || [];
      } catch (error) {
        this.error = error.message;
        this.suggestions = [];
      } finally {
        this.loadingSuggestions = false;
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
