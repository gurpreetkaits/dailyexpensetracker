// src/store/transaction.js
import { defineStore } from "pinia";
import {
  getTransactionById,
  getTransactions,
  getPaginatedTransactions,
  saveTransaction,
  updateTransaction,
  getTransactionStats,
  searchTransactions,
  getActivityBarDataV2
} from "../services/TransactionService";
import axios from "axios";

export const useTransactionStore = defineStore("transaction", {
  state: () => ({
    transactions: [],
    pagination: {
      current_page: 1,
      per_page: 10,
      total: 0,
      last_page: 1,
      from: 0,
      to: 0,
      links: []
    },
    summary: {
      totalExpense: 0,
      totalIncome: 0,
      category: {},
      filteredSummary: {
        income_total: 0,
        expense_total: 0,
      },
    },
    loading: false,
    isLoaded: false,
    currentMonth: new Date().getMonth() + 1,
    currentYear: new Date().getFullYear(),
    userStats: [],
    totalBalance: 0,
    activityBarDataV2: [],
    barTransactions: [],
    selectedBar: null,
  }),
  getters: {
    getFilteredIncome() {
      return this.summary?.filteredSummary.income_total;
    },

    getFilteredExpenses() {
      return this.summary?.filteredSummary.expense_total;
    },
    getBalance() {
      return (
        (this.summary?.totalIncome || 0) - (this.summary?.totalExpense || 0)
      );
    },
    getSavingRate() {
      const totalExpense = this.summary?.totalExpense ?? 0;
      const totalIncome = this.summary?.totalIncome ?? 0;

      // Avoid division by zero
      if (totalIncome === 0) return 0;

      return (((totalIncome - totalExpense) / totalIncome) * 100).toFixed(2);
    },
  },
  actions: {
    async fetchStats(filters) {
      this.loading = true;
      try {
        const data = await getTransactionStats(filters);
        this.userStats = data;
        return data;
      } catch (e) {
        console.log("fetchStats:", e);
      } finally {
        this.loading = false;
      }
    },
    async fetchTransactions(dateFilter) {
      if (this.isLoaded && !dateFilter) return;
      this.loading = true;
      try {
        const { data } = await getTransactions(dateFilter);
        this.transactions = data.transactions;
        this.summary = data.summary;
        this.totalBalance = this.getBalance;
      } catch (error) {
        console.error(error);
      } finally {
        this.loading = false;
        this.isLoaded = true;
      }
    },

    async fetchPaginatedTransactions(page = 1, dateFilter = 'all') {
      this.loading = true;
      try {
        const { data } = await getPaginatedTransactions(page, dateFilter);
        this.transactions = data.transactions.data || [];
        this.pagination = {
          current_page: data.transactions.current_page || 1,
          per_page: data.transactions.per_page || 10,
          total: data.transactions.total || 0,
          last_page: data.transactions.last_page || 1,
          from: data.transactions.from || 0,
          to: data.transactions.to || 0,
          links: data.transactions.links || []
        };
        this.summary = data.summary;
        this.totalBalance = this.getBalance;
        return data;
      } catch (error) {
        console.error('Error fetching paginated transactions:', error);
        this.transactions = [];
        this.pagination = {
          current_page: 1,
          per_page: 10,
          total: 0,
          last_page: 1,
          from: 0,
          to: 0,
          links: []
        };
        return null;
      } finally {
        this.loading = false;
        this.isLoaded = true;
      }
    },
    async addTransaction(transaction) {
      try {
        const { data } = await saveTransaction(transaction);
        const completedTransaction = await getTransactionById(data.id);
        this.transactions.unshift(completedTransaction);
        await this.fetchTransactions();
      } catch (error) {
        console.error(error);
      }
    },
    async updateTransaction(transaction) {
      try {
        const { data } = await updateTransaction(transaction, transaction.id);
        const completedTransaction = await getTransactionById(data.id);
        const index = this.transactions.findIndex(
          (t) => t.id === transaction.id
        );
        this.transactions[index] = {
          ...completedTransaction,
        };
        return data;
      } catch (error) {
        console.error(error);
      }
    },
    removeTransaction(id) {
      this.transactions = this.transactions.filter((t) => t.id !== id);
    },
    async searchTransactions(query, dateFilter) {
      try {
        this.transactions = await searchTransactions(query, dateFilter);
      } catch (error) {
        console.error('Error in search transactions:', error);
        throw error;
      }
    },
    async importTransactions(transactions) {
      try {
        const response = await axios.post('/api/transactions/import', { transactions })
        return response.data
      } catch (error) {
        throw error
      }
    },
    async fetchActivityBarDataV2(period) {
      this.loading = true;
      try {
        const { data } = await getActivityBarDataV2(period);
        this.activityBarDataV2 = data;
        if (data.length > 0) {
          this.selectedBar = { start: data[data.length - 1].start, end: data[data.length - 1].end };
        } else {
          this.selectedBar = null;
        }
      } catch (e) {
        console.error('fetchActivityBarDataV2:', e);
      } finally {
        this.loading = false;
      }
    },
    async fetchBarTransactions(period, bar) {
      this.loading = true;
      try {
        const { barTransactions } = await getActivityBarDataV2(period, bar);
        this.barTransactions = barTransactions;
      } catch (e) {
        console.error('fetchBarTransactions:', e);
      } finally {
        this.loading = false;
      }
    }
  },
});
