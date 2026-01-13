import { defineStore } from "pinia";
import {
  getRecurringExpenses,
  createRecurringExpense,
  updateRecurringExpense,
  deleteRecurringExpense,
  getRecurringSuggestions,
  getLoanDetails,
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
    loadingLoanDetails: false,
    error: null,
    summary: {
      this_month_total: 0,
      active_count: 0,
      subscription_count: 0,
      bill_count: 0,
      emi_count: 0,
      other_count: 0,
      next_payment: null,
      total_remaining_balance: 0,
      emi_payments_made: 0,
      emi_payments_total: 0,
      emi_summary: {
        total_monthly_emi: 0,
        total_loan_amount: 0,
        total_payable: 0,
        total_interest_paid: 0,
        total_interest_remaining: 0,
        total_principal_paid: 0,
        total_principal_remaining: 0,
        overall_completion: 0,
        highest_interest_loan: null,
        soonest_ending_loan: null,
        loans: []
      }
    },
    selectedLoanDetails: null,
    selectedLoanAmortization: []
  }),

  getters: {
    hasEMIs: (state) => state.summary.emi_count > 0,
    emiProgress: (state) => {
      if (state.summary.emi_payments_total === 0) return 0;
      return Math.round((state.summary.emi_payments_made / state.summary.emi_payments_total) * 100);
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

    async fetchLoanDetails(id) {
      this.loadingLoanDetails = true;
      try {
        const response = await getLoanDetails(id);
        this.selectedLoanDetails = response.data.loan;
        this.selectedLoanAmortization = response.data.amortization_schedule;
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loadingLoanDetails = false;
      }
    },

    clearLoanDetails() {
      this.selectedLoanDetails = null;
      this.selectedLoanAmortization = [];
    }
  },
});
