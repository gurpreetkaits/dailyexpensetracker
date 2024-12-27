<template>
    <div class="relative">
        <label class="text-xs font-medium text-gray-600">Category</label>
        <div class="relative">
            <input v-model="categorySearch" type="text"
                class="w-full p-2.5 pl-10 text-sm border border-gray-200 focus:outline-none focus:ring-0 rounded-md bg-gray-50 focus:bg-white transition-colors"
                placeholder="Search category..." @focus="showCategoryDropdown = true" />
            <!-- Selected Category Icon -->
            <div v-if="selectedCategory" class="absolute left-2 top-1/2 -translate-y-1/2 flex items-center">
                <span v-if="!selectableCategory" class="w-6 h-6 rounded-full flex items-center justify-center p-1"
                    :style="{ backgroundColor: selectedCategory.color }">
                    <component :is="selectedCategory.icon" class="w-4 h-4 text-white" />
                </span>
            </div>
            <!-- Clear Button -->
            <button v-if="categorySearch" @click="clearCategory"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <CircleX class="w-4 h-4" />
            </button>
        </div>

        <!-- Dropdown -->
        <div v-show="showCategoryDropdown"
            class="absolute w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto z-10">
            <div v-if="loading" class="px-3 py-2 text-gray-500 flex items-center justify-center">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-900"></div>
                <span class="ml-2">Loading categories...</span>
            </div>
            <div v-if="filteredCategories.length > 0">
                <div v-for="category in filteredCategories" :key="category.id" @click="selectCategory(category)"
                    class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 cursor-pointer">
                    <span class="flex items-center gap-2 w-full">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center p-1"
                            :style="{ backgroundColor: category.color }">
                            <component :is="category.icon" class="w-4 h-4 text-white" />
                        </span>
                        <span>{{ capitalizeFirstLetter(category.name) }}</span>
                    </span>
                </div>
            </div>
            <div v-else class="px-3 py-2 text-gray-500">No categories found</div>
        </div>
    </div>
</template>
<script>
import {
    Calendar, HandCoins, Wallet, ChartCandlestick, Landmark,
    Citrus, ShoppingBag, House, Receipt, Clapperboard, Plane, Contact,
    Cross, ShoppingCart, Book, Gift, BriefcaseBusiness, BadgeDollarSign, Car,
    Dumbbell, Sparkle, CircleDot, CircleX
} from 'lucide-vue-next'
import { useCategoryStore } from '../store/category'
import { numberMixin } from '../mixins/numberMixin';
const iconMap = {
    Wallet, HandCoins, ChartCandlestick, Landmark, Citrus,
    ShoppingBag, House, Receipt, Clapperboard, Plane, Contact,
    Cross, ShoppingCart, Book, Gift, BriefcaseBusiness,
    BadgeDollarSign, Car, Dumbbell, Sparkle, CircleDot, CircleX
}

export default {
    name: 'CategorySearch',
    mixins:[
        numberMixin
    ],
    components: {
        ...iconMap
    },

    props: {
        modelValue: {
            type: [String,Number],
            default: ''
        },
        selectableCategory: {
            type: [Object, String],
            default: null,
            required: false
        }
    },

    data() {
        const store = useCategoryStore();
        return {
            store,
            categorySearch: '',
            showCategoryDropdown: false,
            selectedCategory: null
        }
    },

    computed: {
        categories() {
            return this.store.categories;
        },

        loading() {
            return this.store.loading;
        },

        filteredCategories() {
            if (!this.categories || !this.categories.length) return [];
            if (!this.categorySearch) return this.categories;

            return this.categories.map(cat => ({
                ...cat,
                icon: iconMap[cat.icon]  // Map string icon name to actual component
            })).filter(category =>
                category.name.toLowerCase().includes(this.categorySearch.toLowerCase())
            );
        }
    },

    watch: {
        selectableCategory: {
            immediate: true,
            handler(newCategory) {
                if (newCategory) {
                    this.selectedCategory = newCategory;
                    this.categorySearch = newCategory.name;
                } else {
                    this.selectedCategory = null;
                    this.categorySearch = '';
                }
            }
        }
    },

    methods: {
        selectCategory(category) {
            this.selectedCategory = category;
            this.categorySearch = this.capitalizeFirstLetter(category.name);
            this.$emit('update:modelValue', category.id);
            this.showCategoryDropdown = false;
        },

        clearCategory() {
            this.categorySearch = '';
            this.selectedCategory = null;
            this.$emit('update:modelValue', '');
        },

        handleClickOutside(event) {
            if (!event.target.closest('.relative')) {
                this.showCategoryDropdown = false;
            }
        },

        async loadCategories() {
            try {
                await this.store.fetchCategories();
            } catch (error) {
                console.error('Error loading categories:', error);
            }
        }
    },

    async created() {
        await this.loadCategories();
        if (this.selectableCategory) {
            this.selectedCategory = this.selectableCategory;
            this.categorySearch = this.selectableCategory.name;
        }
    },

    mounted() {
        document.addEventListener('click', this.handleClickOutside);
    },

    beforeDestroy() {
        document.removeEventListener('click', this.handleClickOutside);
    }
}
</script>
<style scoped>
.w-4 {
    width: 1rem;
}

.h-4 {
    height: 1rem;
}
</style>