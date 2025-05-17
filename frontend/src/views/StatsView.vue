<template>
     <div class="max-w-7xl mx-auto relative">
        <div class="space-y-4 relative pb-24 m-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Left: Category Spendings Chart -->
                <CategorySpendingsByChart
                    :categories="topCategories"
                    :loading="loading"
                    :currency-code="currencyCode"
                    :selected-filter="dateFilter"
                    @filter-change="selectQuickFilter"
                />
                <!-- Right: Line Chart -->
                <CategorySpendingComparisonChart
                  :filter="dateFilter"
                  :get-filters="getFilters"
                  :currency-code="currencyCode"
                  :format-currency="formatCurrency"
                />
            </div>
            <!-- Add Transaction Bar -->
            <div class="bg-white rounded-xl shadow-sm px-4 py-3 mb-4 flex items-center justify-between gap-2">
                <div class="flex-shrink-0">
                    <button @click="openAddModal" class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-medium px-5 py-2 rounded-lg shadow transition-all">
                        <Plus class="h-4 w-4" />
                        Add Transaction
                    </button>
                </div>
                <div class="flex-shrink-0">
                    <div v-if="totalPages > 1" class="flex items-center gap-1">
                        <button @click="handlePageChange(currentPage - 1)" :disabled="currentPage === 1" class="px-2 py-1 rounded border text-sm disabled:opacity-50" :class="currentPage === 1 ? 'text-gray-400' : 'text-gray-700 hover:bg-gray-50'">Previous</button>
                        <button v-for="page in displayedPages" :key="page" @click="handlePageChange(page)" class="px-2 py-1 rounded border text-sm" :class="currentPage === page ? 'bg-emerald-500 text-white border-emerald-500' : 'text-gray-700 hover:bg-gray-50'">{{ page }}</button>
                        <button @click="handlePageChange(currentPage + 1)" :disabled="currentPage === totalPages" class="px-2 py-1 rounded border text-sm disabled:opacity-50" :class="currentPage === totalPages ? 'text-gray-400' : 'text-gray-700 hover:bg-gray-50'">Next</button>
                    </div>
                </div>
            </div>
            <!-- Transactions Table -->
            <div>
                <div v-if="paginatedTransactions.length > 0" class="bg-white rounded-xl shadow-sm p-4 overflow-x-auto min-h-[440px]">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-gray-500 border-b">
                                <th class="py-2 text-left font-medium">Category</th>
                                <th class="py-2 text-left font-medium">Type</th>
                                <th class="py-2 text-left font-medium">Note</th>
                                <th class="py-2 text-left font-medium">Amount</th>
                                <th class="py-2 text-left font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="transaction in paginatedTransactions" :key="transaction.id" class="border-b last:border-0">
                                <td class="py-2 flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center" :style="{
                                        backgroundColor: (transaction.category?.color + '15') || (transaction.type === 'income' ? '#e6ffed' : '#ffeded'),
                                        color: transaction.category?.color || (transaction.type === 'income' ? '#16a34a' : '#dc2626')
                                    }">
                                        <span class="text-lg">{{ getCategoryEmoji(transaction.category?.icon) }}</span>
                                    </div>
                                    <span>{{ transaction.category ? capitalizeFirstLetter(transaction.category.name) : '-' }}</span>
                                </td>
                                <td class="py-2">
                                    <span :class="transaction.type === 'expense' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'" class="px-2 py-0.5 rounded-full text-xs capitalize">
                                        {{ transaction.type }}
                                    </span>
                                </td>
                                <td class="py-2">{{ transaction.note }}</td>
                                <td class="py-2">{{ formatCurrency(transaction.amount, currencyCode) }}</td>
                                <td class="py-2">{{ formatDate(transaction.transaction_date) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="bg-white rounded-xl shadow-sm p-4 flex flex-col items-center justify-center min-h-[440px]">
                    <span class="text-gray-400 text-lg font-medium">No Transactions</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import BottomSheet from '../components/BottomSheet.vue'
import { mapActions, mapState } from 'pinia'
import { useTransactionStore } from '../store/transaction'
import { useSettingsStore } from '../store/settings'
import { numberMixin } from '../mixins/numberMixin'
import axios from 'axios'
import { getCategoryTransactions } from '../services/TransactionService'
import { useNotifications } from '../composables/useNotifications'
import AddTransaction from '../components/AddTransaction.vue'
import CategorySpendingsByChart from '../components/Stats/CategorySpendingsByChart.vue'
import VChart from 'vue-echarts'
import * as echarts from 'echarts/core'
import { LineChart } from 'echarts/charts'
import { TitleComponent, TooltipComponent, GridComponent, LegendComponent } from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'
import { iconMixin } from '../mixins/iconMixin'
import CategorySpendingComparisonChart from '../components/Stats/CategorySpendingComparisonChart.vue'

echarts.use([LineChart, TitleComponent, TooltipComponent, GridComponent, LegendComponent, CanvasRenderer])

export default {
    name: 'StatsView',
    components: {
         BottomSheet, AddTransaction, VChart, CategorySpendingsByChart, CategorySpendingComparisonChart
    },
    mixins: [numberMixin, iconMixin],
    setup() {
        const { notify } = useNotifications()
        return { notify }
    },
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
            totalPages: 1,
            currentPage: 1,
            displayedPages: [],
            paginatedTransactions: [],
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
        },
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
                this.notify({
                    title: 'Error',
                    message: error.response?.data?.error || 'Failed to update statistics',
                    type: 'error'
                })
            }
        },

        async openCategoryTransactions(category) {
            this.selectedCategory = category
            this.showCategoryModal = true
            this.loadingCategoryTx = true
            this.categoryTxError = null
            
            try {
                const filters = {
                    ...this.getFilters,
                    category: category.id,
                }
                const response = await getCategoryTransactions(filters)
                this.categoryTransactions = response.transactions
            } catch (error) {
                console.error('Category transactions error:', error)
                this.categoryTxError = error.response?.data?.error || 'Failed to load transactions'
                this.notify({
                    title: 'Error',
                    message: this.categoryTxError,
                    type: 'error'
                })
                this.categoryTransactions = []
            } finally {
                this.loadingCategoryTx = false
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
        },

        async openAddModal() {
            try {
                // Fetch monthly spendings logic
            } catch (error) {
                console.error('Error opening add transaction modal:', error);
                this.notify({
                    title: 'Error',
                    message: error.response?.data?.error || 'Failed to open add transaction modal',
                    type: 'error'
                });
            }
        },

        handlePageChange(page) {
            this.currentPage = page;
            // Fetch monthly spendings logic
        },

        getCategoryEmoji(icon) {
            const emojis = {
                '🍔': '🍔',
                '🏠': '🏠',
                '🚗': '🚗',
                '🎉': '🎉',
                '🍣': '🍣',
                '🍷': '🍷',
                '🍵': '🍵',
                '🍴': '🍴',
                '🎂': '🎂',
                '🎓': '🎓',
                '🏋️': '🏋️',
                '🚴': '🚴',
                '🏃': '🏃',
                '🛌': '🛌',
                '🍳': '🍳',
                '🍕': '🍕',
                '🍩': '🍩',
                '🍪': '🍪',
                '🍫': '🍫',
                '🍬': '🍬',
                '🍭': '🍭',
                '🍮': '🍮',
                '🍯': '🍯',
                '🍰': '🍰',
                '🍱': '🍱',
                '🍲': '🍲',
                '🍳': '🍳',
                '🍴': '🍴',
                '🍵': '🍵',
                '🍶': '🍶',
                '🍷': '🍷',
                '🍸': '🍸',
                '🍹': '🍹',
                '🍺': '🍺',
                '🍻': '🍻',
                '🍼': '🍼',
                '🎂': '🎂',
                '🎃': '🎃',
                '🎄': '🎄',
                '🎅': '🎅',
                '🎆': '🎆',
                '🎇': '🎇',
                '🎈': '🎈',
                '🎉': '🎉',
                '🎊': '🎊',
                '🎋': '🎋',
                '🎌': '🎌',
                '🎍': '🎍',
                '🎎': '🎎',
                '🎏': '🎏',
                '🎐': '🎐',
                '🎑': '🎑',
                '🎒': '🎒',
                '🎓': '🎓',
                '🎖': '🎖',
                '🎗': '🎗',
                '🎘': '🎘',
                '🎙': '🎙',
                '🎚': '🎚',
                '🎛': '🎛',
                '🎜': '🎜',
                '🎝': '🎝',
                '🎞': '🎞',
                '🎟': '🎟',
                '🎠': '🎠',
                '🎡': '🎡',
                '🎢': '🎢',
                '🎣': '🎣',
                '🎤': '🎤',
                '🎥': '🎥',
                '🎦': '🎦',
                '🎧': '🎧',
                '🎨': '🎨',
                '🎩': '🎩',
                '🎪': '🎪',
                '🎫': '🎫',
                '🎬': '🎬',
                '🎭': '🎭',
                '🎮': '🎮',
                '🎯': '🎯',
                '🎰': '🎰',
                '🎱': '🎱',
                '🎲': '🎲',
                '🎳': '🎳',
                '🎴': '🎴',
                '🎵': '🎵',
                '🎶': '🎶',
                '🎷': '🎷',
                '🎸': '🎸',
                '🎹': '🎹',
                '🎺': '🎺',
                '🎻': '🎻',
                '🎼': '🎼',
                '🎽': '🎽',
                '🎾': '🎾',
                '🎿': '🎿',
                '🏀': '🏀',
                '🏁': '🏁',
                '🏂': '🏂',
                '🏃': '🏃',
                '🏄': '🏄',
                '🏅': '🏅',
                '🏆': '🏆',
                '🏇': '🏇',
                '🏈': '🏈',
                '🏉': '🏉',
                '🏊': '🏊',
                '🏋️': '🏋️',
                '🏌': '🏌',
                '🏍': '🏍',
                '🏎': '🏎',
                '🏏': '🏏',
                '🏐': '🏐',
                '🏑': '🏑',
                '🏒': '🏒',
                '🏓': '🏓',
                '🏔': '🏔',
                '🏕': '🏕',
                '🏖': '🏖',
                '🏗': '🏗',
                '🏘': '🏘',
                '🏙': '🏙',
                '🏚': '🏚',
                '🏛': '🏛',
                '🏜': '🏜',
                '🏝': '🏝',
                '🏞': '🏞',
                '🏟': '🏟',
                '🏠': '🏠',
                '🏡': '🏡',
                '🏢': '🏢',
                '🏣': '🏣',
                '🏤': '🏤',
                '🏥': '🏥',
                '🏦': '🏦',
                '🏧': '🏧',
                '🏨': '🏨',
                '🏩': '🏩',
                '🏪': '🏪',
                '🏫': '🏫',
                '🏬': '🏬',
                '🏭': '🏭',
                '🏮': '🏮',
                '🏯': '🏯',
                '🏰': '🏰',
                '🏱': '🏱',
                '🏳': '🏳',
                '🏴': '🏴',
                '🏵': '🏵',
                '🏶': '🏶',
                '🏷': '🏷',
                '🏸': '🏸',
                '🏹': '🏹',
                '🏺': '🏺',
                '🏻': '🏻',
                '🏼': '🏼',
                '🏽': '🏽',
                '🏾': '🏾',
                '🏿': '🏿'
            };
            return emojis[icon] || '❓';
        },
    },

    async created() {
        try {
            await this.fetchStats(this.getFilters)
        } catch (error) {
            console.error('Error in StatsView created:', error)
            this.notify({
                title: 'Error',
                message: error.response?.data?.error || 'Failed to load statistics',
                type: 'error'
            })
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
