// src/store/category.js
import { defineStore } from "pinia";
import axios from "../config/axiosConf";
import { getCategories } from "../services/SettingsService";

export const useCategoryStore = defineStore("category", {
    state: () => ({
        categories: [],
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
        async fetchCategories() {
            this.loading = true;
            try {
                const data  = await getCategories();
                this.categories = data;
                return data;
            } catch (error) {
                this.error = error.message;
                console.error("Error fetching categories:", error);
                return [];
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