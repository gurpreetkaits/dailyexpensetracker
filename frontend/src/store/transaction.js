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
  getActivityBarDataV2,
  getDailyBarData
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
    filters: {
      filter: 'all',
      type: 'all',
      wallet_id: null,
      category_id: null,
      search: '',
      date_from: null,
      date_to: null
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
    dailyBarData: [],
    dailyBarMeta: null,
    selectedDayBar: null,
    dailyBarFilters: {
      start_date: null,
      end_date: null,
    },
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

    async fetchPaginatedTransactions(page = 1, filters = {}) {
      this.loading = true;

      // Merge default filters with provided filters
      const appliedFilters = {
        ...this.filters,
        ...filters,
        page
      };

      // Remove null/undefined/empty string values
      Object.keys(appliedFilters).forEach(key => {
        if (appliedFilters[key] === null || appliedFilters[key] === undefined || appliedFilters[key] === '') {
          delete appliedFilters[key];
        }
      });

      try {
        const { data } = await getPaginatedTransactions(page, appliedFilters);
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

        // Update filters state with applied filters
        this.filters = { ...this.filters, ...filters };

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

    updateFilters(filters) {
      this.filters = { ...this.filters, ...filters };
      return this.fetchPaginatedTransactions(1, this.filters);
    },

    resetFilters() {
      this.filters = {
        filter: 'all',
        type: 'all',
        wallet_id: null,
        category_id: null,
        search: '',
        date_from: null,
        date_to: null
      };
      return this.fetchPaginatedTransactions(1, this.filters);
    },
    async addTransaction(transaction) {
      try {
        const { data } = await saveTransaction(transaction);
        const completedTransaction = await getTransactionById(data.id);
        this.transactions.unshift(completedTransaction);
        await this.fetchTransactions();
        return { success: true };
      } catch (error) {
        console.error(error);
        return {
          success: false,
          errors: error.response?.data?.errors || { general: [{ message: 'An error occurred while saving the transaction.' }] }
        };
      }
    },
    async updateTransaction(transaction) {
      try {
        const { data } = await updateTransaction(transaction, transaction.id);
        const completedTransaction = await getTransactionById(data.id);

        // Update in main transactions array using Vue's reactivity system
        const index = this.transactions.findIndex(
          (t) => t.id === transaction.id
        );

        if (index !== -1) {
          // Create a new array with the updated transaction
          this.transactions = [
            ...this.transactions.slice(0, index),
            completedTransaction,
            ...this.transactions.slice(index + 1)
          ];
        }

        return { success: true, data: completedTransaction };
      } catch (error) {
        console.error(error);
        return {
          success: false,
          errors: error.response?.data?.errors || { general: [{ message: 'An error occurred while updating the transaction.' }] }
        };
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
    async fetchBarTransactions(period, bar = null) {
      this.loading = true;
      try {
        const { barTransactions } = await getActivityBarDataV2(period, bar);
            this.barTransactions = barTransactions;
      } catch (e) {
        console.error('fetchBarTransactions:', e);
      } finally {
        this.loading = false;
      }
    },
    async fetchDailyBarData(params = {}) {
      this.loading = true;
      try {
        const response = await getDailyBarData(params);
        this.dailyBarData = response.data;
        this.dailyBarMeta = response.meta;
        this.dailyBarFilters = {
          start_date: response.meta.start_date,
          end_date: response.meta.end_date,
        };
        if (response.data.length > 0 && !this.selectedDayBar) {
          this.selectedDayBar = response.data[response.data.length - 1];
        }
      } catch (e) {
        console.error('fetchDailyBarData:', e);
      } finally {
        this.loading = false;
      }
    },
    async loadMoreDailyBarData() {
      if (!this.dailyBarMeta?.has_more || this.loading) return;

      this.loading = true;
      try {
        const currentStartDate = new Date(this.dailyBarMeta.start_date);
        const newEndDate = new Date(currentStartDate);
        newEndDate.setDate(newEndDate.getDate() - 1);

        const newStartDate = new Date(newEndDate);
        newStartDate.setDate(newStartDate.getDate() - 59);

        const response = await getDailyBarData({
          start_date: newStartDate.toISOString().split('T')[0],
          end_date: newEndDate.toISOString().split('T')[0],
        });

        // Prepend older data to the beginning
        this.dailyBarData = [...response.data, ...this.dailyBarData];
        this.dailyBarMeta = {
          ...response.meta,
          end_date: this.dailyBarMeta.end_date, // Keep the original end date
        };
        this.dailyBarFilters.start_date = response.meta.start_date;
      } catch (e) {
        console.error('loadMoreDailyBarData:', e);
      } finally {
        this.loading = false;
      }
    },
    async fetchDailyBarDataByDateRange(startDate, endDate) {
      this.loading = true;
      try {
        const response = await getDailyBarData({
          start_date: startDate,
          end_date: endDate,
        });
        this.dailyBarData = response.data;
        this.dailyBarMeta = response.meta;
        this.dailyBarFilters = {
          start_date: startDate,
          end_date: endDate,
        };
        if (response.data.length > 0) {
          this.selectedDayBar = response.data[response.data.length - 1];
        }
      } catch (e) {
        console.error('fetchDailyBarDataByDateRange:', e);
      } finally {
        this.loading = false;
      }
    },
    setSelectedDayBar(day) {
      this.selectedDayBar = day;
    },
    clearDailyBarFilters() {
      this.dailyBarFilters = {
        start_date: null,
        end_date: null,
      };
    }
  },
});
