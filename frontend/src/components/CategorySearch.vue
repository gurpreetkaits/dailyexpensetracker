<template>
    <div class="relative">
        <label class="text-xs font-medium text-gray-600">Category</label>
        <div class="relative">
            <input v-model="categorySearch" type="text"
                class="w-full p-2.5 pl-10 text-sm border border-gray-200 focus:outline-none focus:ring-0 rounded-md bg-gray-50 focus:bg-white transition-colors"
                placeholder="Search category..." @focus="showCategoryDropdown = true" />
            <!-- Selected Category Icon -->
            <div v-if="selectedCategory" class="absolute left-2 top-1/2 -translate-y-1/2 flex items-center">
                <span v-if="!selectableCategory" 
                    class="w-6 h-6 rounded-full flex items-center justify-center p-1"
                    :style="{ backgroundColor: `${selectedCategory.color}` }">
                    <span class="text-base" :style="{ color: selectedCategory.color }">
                        {{ getCategoryEmoji(selectedCategory.icon) }}
                    
                    </span>
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
            class="absolute w-full mt-1 bg-white border rounded-md shadow-lg max-h-48 overflow-y-auto z-20">
            <div v-if="loading" class="px-3 py-2 text-gray-500 flex items-center justify-center">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-900"></div>
                <span class="ml-2">Loading categories...</span>
            </div>
            <div v-if="filteredCategories.length > 0">
                <div v-for="category in filteredCategories" :key="category.id" @click="selectCategory(category)"
                    class="flex items-center gap-2 px-3 py-2 hover:bg-gray-50 cursor-pointer">
                    <span class="flex items-center gap-2 w-full">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center p-1"
                            :style="{ backgroundColor: `${category.color}15` }">
                            <span class="text-base" :style="{ color: category.color }">
                                {{ getCategoryEmoji(category.icon) }}
                            </span>
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
import { useCategoryStore } from '../store/category'
import { numberMixin } from '../mixins/numberMixin'
import { useSettingsStore } from '../store/settings'
import { emojiMixin } from '../mixins/emojiMixin'
import { CircleX } from 'lucide-vue-next'

export default {
    name: 'CategorySearch',
    mixins: [
        numberMixin,
        emojiMixin
    ],
    components: {
        CircleX
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
        },
        formType: {
            type: String,
            default: 'expense',
            required: false
        }
    },

    data() {
        const settingStore = useSettingsStore()
        return {
            settingStore,
            categorySearch: '',
            showCategoryDropdown: false,
            selectedCategory: null
        }
    },

    computed: {
        categories() {
            return this.settingStore.categories
        },

        loading() {
            return this.settingStore.loading
        },

        filteredCategories() {
            if (!this.categories || !this.categories.length) return []
            if (!this.categorySearch) return this.categories
                console.log('categories', this.categories)
            return this.categories.filter(category =>
                category.name.toLowerCase().includes(this.categorySearch.toLowerCase()) &&
                category.type.toLowerCase() === this.formType.toLowerCase()
            )
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
                const categories = await this.settingStore.fetchCategories();
                console.log("categories", categories)
                if (!categories || categories.length === 0) {
                    console.warn('CategorySearch: No categories received');
                }
            } catch (error) {
                console.error('CategorySearch: Error loading categories:', error);
            }
        }
    },

    async created() {
        console.log('CategorySearch: Component created');
        await this.loadCategories();
        if (this.selectableCategory) {
            console.log('CategorySearch: Setting selectable category:', this.selectableCategory);
            this.selectedCategory = this.selectableCategory;
            this.categorySearch = this.selectableCategory.name;
        }
    },

    mounted() {
        document.addEventListener('click', this.handleClickOutside);
    },

    beforeDestroy() {
        document.removeEventListener('click', this.handleClickOutside);
    },
    getCategoryEmoji(icon) {
        if (!icon) return '📌'
        return this.emojis.income[icon.toLowerCase()] || this.emojis.expense[icon.toLowerCase()] || '📌'
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

/* Add styles for emoji container */
.text-base {
    line-height: 1;
    display: inline-block;
    transform: translateY(1px);
}

/* Add transition for smoother color changes */
.w-6 {
    transition: background-color 0.2s ease;
}
</style>
