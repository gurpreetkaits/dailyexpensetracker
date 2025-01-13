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
  summary: {
      monthly_payment_total: 0,
      total_amount_paid: 0,
      total_interest_paid: 0,
      total_pending_payments: 0,
      total_remaining_balance: 0,
      upcoming_payments: []
  }
  }),

  actions: {
    async fetchRecurringExpenses() {
      this.loading = true;
      try {
        const response = await getRecurringExpenses();
        console.log('response',response)
        this.recurringExpenses = response.data.recurring_expenses;
        this.summary = response.data.total;
        console.log('this.details',this.details)
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
