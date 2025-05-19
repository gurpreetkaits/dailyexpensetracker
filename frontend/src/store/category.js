// src/store/category.js
import { defineStore } from "pinia";
import axios from "../config/axiosConf";
import { getCategories } from "../services/SettingsService";
import { getUserCategories } from "../services/CategoryService";

export const useCategoryStore = defineStore("category", {
    state: () => ({
        categories: [],
        allCategories: [],
        pagination: {
            current_page: 1,
            per_page: 10,
            total: 0,
            last_page: 1,
            from: 1,
            to: 1
        },
        loading: false,
        error: null,
        selectedCategory: null
    }),

    getters: {
        incomeCategories: (state) => 
            state.categories.filter(cat => cat.type === 'income'),
            
        expenseCategories: (state) => 
            state.categories.filter(cat => cat.type === 'expense'),
            
        getCategoryById: (state) => (id) => 
            state.categories.find(cat => cat.id === id)
    },

    actions: {
        async fetchCategories(page = 1) {
            this.loading = true;
            try {
                const response = await getUserCategories(page);
                this.categories = response.data;
                this.pagination = {
                    current_page: response.current_page,
                    per_page: response.per_page,
                    total: response.total,
                    last_page: response.last_page,
                    from: response.from,
                    to: response.to,
                    links: response.links
                };
                return response;
            } catch (error) {
                this.error = error.message;
                console.error("Error fetching categories:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async addCategory(category) {
            this.loading = true;
            try {
                const { data } = await axios.post('/api/categories', category);
                this.categories.push(data);
                return data;
            } catch (error) {
                this.error = error.message;
                console.error("Error adding category:", error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateCategory(id, category) {
            this.loading = true;
            try {
                const { data } = await axios.put(`/api/categories/${id}`, category);
                const index = this.categories.findIndex(c => c.id === id);
                if (index !== -1) {
                    this.categories[index] = data;
                }
                return data;
            } catch (error) {
                this.error = error.message;
                console.error("Error updating category:", error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteCategory(id) {
            this.loading = true;
            try {
                await axios.delete(`/api/categories/${id}`);
                this.categories = this.categories.filter(c => c.id !== id);
            } catch (error) {
                this.error = error.message;
                console.error("Error deleting category:", error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        setSelectedCategory(category) {
            this.selectedCategory = category;
        },

        clearSelectedCategory() {
            this.selectedCategory = null;
        },

        clearError() {
            this.error = null;
        }
    },
});