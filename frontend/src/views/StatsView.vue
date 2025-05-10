<template>
    <div class="space-y-4 relative pb-24 m-3 lg:max-w-2xl lg:mx-auto">
        <!-- Overview Card -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-semibold text-red-500">{{ formatCurrency(totalSpent, currencyCode) }}</h2>
                    <p class="text-sm text-gray-500">Total Spent</p>
                </div>
                <div class="h-10 w-10 bg-red-100 rounded-full flex items-center justify-center">
                    <ShoppingCart class="h-5 w-5 text-red-500" />
                </div>
            </div>
            <!-- Date Filter Pills -->
            <div class="flex gap-2 overflow-x-auto">
                <button v-for="filter in quickFilters" :key="filter.value" @click="selectQuickFilter(filter.value)"
                    class="px-3 py-1.5 rounded-full text-xs whitespace-nowrap transition-all" :class="dateFilter === filter.value ?
                        'bg-blue-500 text-white' :
                        'bg-gray-100 text-gray-600'">
                    {{ filter.label }}
                </button>
            </div>

            <!-- Custom Date Range (Simplified) -->
            <div v-if="dateFilter === 'custom'" class="mt-3 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" v-model="customStartDate"
                        class="w-full p-1.5 text-xs border rounded-lg bg-gray-50">
                    <input type="date" v-model="customEndDate"
                        class="w-full p-1.5 text-xs border rounded-lg bg-gray-50">
                </div>
                <button @click="applyFilter" class="w-full bg-blue-500 text-white rounded-lg py-1.5 text-xs">
                    Apply
                </button>
            </div>
        </div>
        <!-- Financial Health Card -->
        <div v-if="!loading" class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Financial Health</h3>
                <div class="px-4 py-1 rounded-full text-sm font-medium" :class="{
                    'bg-emerald-100 text-emerald-700': financialHealth === 'Excellent',
                    'bg-blue-100 text-blue-700': financialHealth === 'Good',
                    'bg-yellow-100 text-yellow-700': financialHealth === 'Fair',
                    'bg-red-100 text-red-700': financialHealth === 'Poor'
                }">
                    {{ financialHealth }}
                </div>
            </div>
            <div class="space-y-3">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Savings Rate</span>
                        <span class="text-sm font-medium">{{ savingsRate }}%</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full transition-all duration-500" :class="{
                            'bg-emerald-500': savingsRate >= 20,
                            'bg-blue-500': savingsRate >= 15 && savingsRate < 20,
                            'bg-yellow-500': savingsRate >= 10 && savingsRate < 15,
                            'bg-red-500': savingsRate < 10
                        }" :style="{ width: `${savingsRate}%` }">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Breakdown -->
        <div v-if="!loading" class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-lg font-semibold mb-6">Top Categories</h2>
            <div v-if="!topCategories.length" class="text-center py-8 text-gray-500 flex flex-col items-center">
                <ShoppingBag class="h-12 w-12 mb-3 text-gray-300" />
                No Spending Found :)
            </div>
            <div class="space-y-6">
                <div v-for="category in topCategories" :key="category.id"
                    class="space-y-2 hover:bg-gray-50 p-3 rounded-xl transition-colors cursor-pointer"
                    @click="openCategoryTransactions(category)">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                :style="{ backgroundColor: category.color + '15', color: category.color }">
                                <component :is="getCategoryIcon(category)" class="h-5 w-5" />
                            </div>
                            <div>
                                <span class="font-medium text-gray-900">{{ category.name }}</span>
                                <p class="text-sm text-gray-500">{{ category.percentage }}% of spending</p>
                            </div>
                        </div>
                        <span class="font-semibold text-gray-900">{{ formatCurrency(category.amount, currencyCode)
                            }}</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full transition-all duration-500 rounded-full"
                            :style="{ width: `${category.percentage}%`, backgroundColor: category.color }">
                        </div>
                    </div>
                </div>
                <!-- Category Transactions Modal (Desktop) -->
                <div v-if="showCategoryModal && !isMobile" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
                  <div class="bg-white rounded-xl shadow max-w-md w-full p-0 relative">
                    <button @click="closeCategoryModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">
                      <CircleX class="h-6 w-6" />
                    </button>
                    <h3 class="text-base font-semibold px-4 pt-4 pb-2">Transactions for {{ selectedCategory?.name }}</h3>
                    <div v-if="loadingCategoryTx" class="text-center py-8 text-gray-400">Loading...</div>
                    <div v-else-if="categoryTxError" class="text-center py-8 text-red-400">{{ categoryTxError }}</div>
                    <div v-else-if="categoryTransactions.length === 0" class="text-gray-500 text-center py-8">
                      No transactions found for this category.
                    </div>
                    <div v-else class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                      <div v-for="tx in categoryTransactions" :key="tx.id" class="py-2 px-4 flex items-center gap-2 text-xs">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center" :style="{ backgroundColor: selectedCategory.color + '15', color: selectedCategory.color }">
                          <component :is="getCategoryIcon(selectedCategory)" class="h-4 w-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                          <div class="font-medium text-gray-900 truncate">{{ tx.note || 'No note' }}</div>
                          <div class="text-xs text-gray-400 truncate">{{ formatDate(tx.transaction_date) }}</div>
                        </div>
                        <div class="text-right">
                          <span class="font-medium text-red-600">-{{ formatCurrency(tx.amount, currencyCode) }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Category Transactions BottomSheet (Mobile) -->
                <BottomSheet v-if="showCategoryModal && isMobile" v-model="showCategoryModal">
                  <div class="p-2">
                    <div class="flex justify-between items-center mb-2">
                      <h3 class="text-base font-semibold">Transactions for {{ selectedCategory?.name }}</h3>
                      <button @click="closeCategoryModal" class="text-gray-400 hover:text-gray-700">
                        <CircleX class="h-6 w-6" />
                      </button>
                    </div>
                    <div v-if="loadingCategoryTx" class="text-center py-8 text-gray-400">Loading...</div>
                    <div v-else-if="categoryTxError" class="text-center py-8 text-red-400">{{ categoryTxError }}</div>
                    <div v-else-if="categoryTransactions.length === 0" class="text-gray-500 text-center py-8">
                      No transactions found for this category.
                    </div>
                    <div v-else class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                      <div v-for="tx in categoryTransactions" :key="tx.id" class="py-2 flex items-center gap-2 text-xs">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center" :style="{ backgroundColor: selectedCategory.color + '15', color: selectedCategory.color }">
                          <component :is="getCategoryIcon(selectedCategory)" class="h-4 w-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                          <div class="font-medium text-gray-900 truncate">{{ tx.note || 'No note' }}</div>
                          <div class="text-xs text-gray-400 truncate">{{ formatDate(tx.transaction_date) }}</div>
                        </div>
                        <div class="text-right">
                          <span class="font-medium text-red-600">-{{ formatCurrency(tx.amount, currencyCode) }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </BottomSheet>
            </div>
        </div>
    </div>
</template>

<script>
import {
    Calendar, Trash2, Plus, Car, ReceiptIcon, Video, BriefcaseMedical, Gift, Circle, CircleEllipsis, Pizza, CircleDollarSign
    , HandCoins, Wallet, ChartCandlestick, Landmark,
    Citrus, ShoppingBag, House, Receipt, Clapperboard, Plane, Contact,
    Cross, ShoppingCart, Book, BriefcaseBusiness, BadgeDollarSign,
    Dumbbell, CalendarDays,
    Sparkle,
    CircleDot, CircleX
} from 'lucide-vue-next'
import BottomSheet from '../components/BottomSheet.vue'
import { mapActions, mapState } from 'pinia'
import { useTransactionStore } from '../store/transaction'
import { useSettingsStore } from '../store/settings'
import { numberMixin } from '../mixins/numberMixin'
import axios from 'axios'
import { getCategoryTransactions } from '../services/TransactionService'

export default {
    name: 'StatsPage',
    components: {
        Calendar, Trash2, Plus, Car, ReceiptIcon, Video, BriefcaseMedical, Gift, Circle, CircleEllipsis, Pizza, CircleDollarSign
        , HandCoins, Wallet, ChartCandlestick, Landmark,
        Citrus, ShoppingBag, House, Receipt, Clapperboard, Plane, Contact,
        Cross, ShoppingCart, Book, BriefcaseBusiness, BadgeDollarSign,
        Dumbbell, CalendarDays,
        Sparkle,
        CircleDot, CircleX, BottomSheet
    },
    mixins: [numberMixin],
    data() {
        return {
            showFilterModal: false,
            dateFilter: 'month',
            customStartDate: '',
            customEndDate: '',
            quickFilters: [
                // { label: 'Today', value: 'today' },
                { label: 'Weekly', value: 'week' },
                { label: 'Monthly', value: 'month' },
                { label: 'Yearly', value: 'year' },
                { label: 'Custom', value: 'custom' },
            ],
            showCategoryModal: false,
            selectedCategory: null,
            categoryTransactions: [],
            loadingCategoryTx: false,
            categoryTxError: null,
        }
    },

    computed: {
        ...mapState(useTransactionStore, ['userStats', 'loading']),
        ...mapState(useSettingsStore, ['currencyCode']),
        isMobile() {
            return window.innerWidth < 640;
        },
        totalSpent() {
            return this.userStats?.overview?.total_spent || 0
        },
        averagePerDay() {
            return this.userStats?.overview?.avg_per_day || 0
        },
        percentageChange() {
            return this.userStats?.overview?.previous_comparison || 0
        },

        averageChange() {
            return this.userStats?.average_change || 0
        },

        savingsRate() {
            return this.userStats?.financial_health?.savings_rate || 0
        },

        financialHealth() {
            return this.userStats?.financial_health?.status?.status || 'Good'
        },

        topCategories() {
            return this.userStats?.categories?.map(cat => ({
                id: cat.id,
                name: this.capitalizeFirstLetter(cat.name),
                amount: cat.amount,
                percentage: cat.percentage,
                color: cat.color || '#2563eb',
                icon: cat.icon
            })) || []
        },

        dateFilterLabel() {
            const labels = {
                today: 'Today',
                week: 'This Week',
                month: 'This Month',
                year: 'This Year',
                all: 'All Time',
                custom: this.customDateLabel
            }
            return labels[this.dateFilter] || 'This Month'
        },

        customDateLabel() {
            if (!this.customStartDate || !this.customEndDate) return 'Custom Range'
            return `${new Date(this.customStartDate).toLocaleDateString('en-IN', {
                month: 'short',
                day: 'numeric'
            })} - ${new Date(this.customEndDate).toLocaleDateString('en-IN', {
                month: 'short',
                day: 'numeric'
            })}`
        },

        getFilters() {
            let { start_date, end_date } = this.getDateRangeFromType(this.dateFilter);
            return {
                start_date,
                end_date,
                // add other filters if needed
            };
        }
    },

    methods: {
        ...mapActions(useTransactionStore, ['fetchStats']),

        formatNumber(value) {
            return new Intl.NumberFormat('en-IN').format(value)
        },

        getCategoryIcon(category) {
            return category.icon
        },

        getHealthColor(health) {
            const colors = {
                'Excellent': 'text-green-500',
                'Good': 'text-blue-500',
                'Fair': 'text-yellow-500',
                'Poor': 'text-red-500'
            }
            return colors[health] || 'text-gray-500'
        },

        selectQuickFilter(filter) {
            this.dateFilter = filter
            if (filter !== 'custom') {
                this.applyFilter()
            }
        },

        async applyFilter() {
            try {
                await this.fetchStats(this.getFilters)
                this.showFilterModal = false
            } catch (error) {
                console.error('Error applying filter:', error)
            }
        },

        async openCategoryTransactions(category) {
            this.selectedCategory = category;
            this.showCategoryModal = true;
            this.loadingCategoryTx = true;
            this.categoryTxError = null;
            try {
                const filters = {
                    ...this.getFilters,
                    category: category.id,
                }
                const response = await getCategoryTransactions(filters);
                this.categoryTransactions = response.transactions;
            } catch (err) {
                this.categoryTxError = 'Failed to load transactions.';
                this.categoryTransactions = [];
            } finally {
                this.loadingCategoryTx = false;
            }
        },

        closeCategoryModal() {
            this.showCategoryModal = false;
            this.selectedCategory = null;
            this.categoryTransactions = [];
            this.categoryTxError = null;
        },

        getDateRangeFromType(type) {
            const now = new Date();
            let start, end;

            switch (type) {
                case 'today':
                    start = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    end = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
                    break;
                case 'week':
                case 'weekly':
                    // Start of week (Sunday)
                    start = new Date(now);
                    start.setDate(now.getDate() - now.getDay());
                    start.setHours(0, 0, 0, 0);
                    end = new Date(start);
                    end.setDate(start.getDate() + 7);
                    break;
                case 'month':
                case 'monthly':
                    start = new Date(now.getFullYear(), now.getMonth(), 1);
                    end = new Date(now.getFullYear(), now.getMonth() + 1, 1);
                    break;
                case 'year':
                case 'yearly':
                    start = new Date(now.getFullYear(), 0, 1);
                    end = new Date(now.getFullYear() + 1, 0, 1);
                    break;
                case 'yesterday':
                    start = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
                    end = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    break;
                case 'custom':
                    // Use customStartDate and customEndDate from your data
                    start = this.customStartDate ? new Date(this.customStartDate) : null;
                    end = this.customEndDate ? new Date(this.customEndDate) : null;
                    break;
                default:
                    start = null;
                    end = null;
            }

            // Format as YYYY-MM-DD
            const format = d => d ? d.toISOString().slice(0, 10) : null;
            return {
                start_date: format(start),
                end_date: format(end)
            };
        }
    },

    async created() {
        try {
            await this.fetchStats(this.getFilters)
        } catch (error) {
            console.error('Error in StatsView created:', error)
        }
    }
}
</script>

<style scoped>
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

.hover\:shadow-lg:hover {
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
}
</style>
