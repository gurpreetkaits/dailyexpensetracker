<template>
    <div class="space-y-3 sm:space-y-4 pb-safe">
        <!-- Minimal Header -->
        <div class="flex justify-between items-center">
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">
                {{ editingExpense ? 'Edit Expense' : 'New Recurring' }}
            </h2>
            <button @click="$emit('cancel')" class="p-2 -mr-2 rounded-lg hover:bg-gray-100 text-gray-400 active:bg-gray-200">
                <X class="h-5 w-5" />
            </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-3 sm:space-y-4">
            <!-- Type Selection - Touch friendly -->
            <div>
                <div class="flex items-center gap-1 mb-2">
                    <span class="text-xs text-gray-500">Type</span>
                    <span class="w-1 h-1 rounded-full bg-red-500"></span>
                </div>
                <div class="grid grid-cols-4 gap-1.5 sm:gap-2">
                    <button
                        v-for="(config, type) in typeConfig"
                        :key="type"
                        type="button"
                        @click="form.type = type"
                        class="flex flex-col items-center justify-center gap-1 py-2.5 sm:py-2 rounded-xl text-xs font-medium transition-all active:scale-95"
                        :class="form.type === type
                            ? `${config.selectedBg} ${config.selectedText} ring-2 ${config.ringColor}`
                            : 'bg-gray-50 text-gray-500 hover:bg-gray-100 active:bg-gray-200'"
                    >
                        <component :is="config.icon" class="h-4 w-4 sm:h-3.5 sm:w-3.5" />
                        <span class="text-[10px] sm:text-xs">{{ config.label }}</span>
                    </button>
                </div>
            </div>

            <!-- Quick Presets - Scrollable chips -->
            <div v-if="!editingExpense && filteredPresets.length > 0" class="-mx-4 px-4 sm:mx-0 sm:px-0">
                <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                    <button
                        v-for="preset in filteredPresets"
                        :key="preset.name"
                        type="button"
                        @click="applyPreset(preset)"
                        class="flex-shrink-0 px-3 py-1.5 text-xs rounded-full transition-all active:scale-95"
                        :class="selectedPreset === preset.name
                            ? 'bg-gray-900 text-white'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 active:bg-gray-300'"
                    >
                        {{ preset.name }}
                    </button>
                </div>
            </div>

            <!-- Name -->
            <div>
                <div class="flex items-center gap-1 mb-1.5">
                    <span class="text-xs text-gray-500">Name</span>
                    <span class="w-1 h-1 rounded-full bg-red-500"></span>
                </div>
                <input
                    v-model="form.name"
                    type="text"
                    class="w-full px-3 py-3 sm:py-2.5 bg-gray-50 border-0 rounded-xl text-base sm:text-sm placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20"
                    :placeholder="namePlaceholder"
                    required
                />
            </div>

            <!-- EMI Fields -->
            <div v-if="form.type === 'emi'" class="bg-amber-50/50 rounded-xl p-3 space-y-3">
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <div class="flex items-center gap-1 mb-1">
                            <span class="text-[10px] text-amber-700">Principal</span>
                            <span class="w-1 h-1 rounded-full bg-red-500"></span>
                        </div>
                        <input
                            v-model.number="form.principal_amount"
                            type="number"
                            inputmode="numeric"
                            class="w-full px-2 py-2.5 sm:py-2 bg-white border border-amber-200 rounded-lg text-base sm:text-sm focus:ring-2 focus:ring-amber-400/50 focus:border-amber-300"
                            placeholder="100000"
                            required
                        />
                    </div>
                    <div>
                        <div class="flex items-center gap-1 mb-1">
                            <span class="text-[10px] text-amber-700">Rate %</span>
                            <span class="w-1 h-1 rounded-full bg-red-500"></span>
                        </div>
                        <input
                            v-model.number="form.interest_rate"
                            type="number"
                            inputmode="decimal"
                            step="0.01"
                            class="w-full px-2 py-2.5 sm:py-2 bg-white border border-amber-200 rounded-lg text-base sm:text-sm focus:ring-2 focus:ring-amber-400/50 focus:border-amber-300"
                            placeholder="12"
                            required
                        />
                    </div>
                    <div>
                        <div class="flex items-center gap-1 mb-1">
                            <span class="text-[10px] text-amber-700">Months</span>
                            <span class="w-1 h-1 rounded-full bg-red-500"></span>
                        </div>
                        <input
                            v-model.number="form.tenure_months"
                            type="number"
                            inputmode="numeric"
                            class="w-full px-2 py-2.5 sm:py-2 bg-white border border-amber-200 rounded-lg text-base sm:text-sm focus:ring-2 focus:ring-amber-400/50 focus:border-amber-300"
                            placeholder="36"
                            required
                        />
                    </div>
                </div>
                <!-- EMI Result -->
                <div v-if="emiDetails" class="flex items-center justify-between pt-2 border-t border-amber-200">
                    <span class="text-xs text-amber-700">Monthly EMI</span>
                    <span class="text-lg font-bold text-amber-800">{{ displayCurrencySymbol }}{{ formatNumber(emiDetails.monthlyEMI) }}</span>
                </div>
            </div>

            <!-- Amount & Frequency -->
            <div class="grid grid-cols-2 gap-2 sm:gap-3">
                <div>
                    <div class="flex items-center gap-1 mb-1.5">
                        <span class="text-xs text-gray-500">Amount</span>
                        <span class="w-1 h-1 rounded-full bg-red-500"></span>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">{{ displayCurrencySymbol }}</span>
                        <input
                            v-model.number="form.amount"
                            type="number"
                            inputmode="decimal"
                            step="0.01"
                            class="w-full pl-8 pr-3 py-3 sm:py-2.5 bg-gray-50 border-0 rounded-xl text-base sm:text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20"
                            placeholder="0.00"
                            :readonly="form.type === 'emi'"
                            :class="{ 'opacity-60': form.type === 'emi' }"
                            required
                        />
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-1 mb-1.5">
                        <span class="text-xs text-gray-500">Frequency</span>
                        <span class="w-1 h-1 rounded-full bg-red-500"></span>
                    </div>
                    <select
                        v-model="form.recurrence"
                        class="w-full px-3 py-3 sm:py-2.5 bg-gray-50 border-0 rounded-xl text-base sm:text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 appearance-none"
                        :disabled="form.type === 'emi'"
                        :class="{ 'opacity-60': form.type === 'emi' }"
                    >
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
            </div>

            <!-- Day & Date -->
            <div class="grid grid-cols-2 gap-2 sm:gap-3">
                <div>
                    <div class="flex items-center gap-1 mb-1.5">
                        <span class="text-xs text-gray-500">Day</span>
                        <span class="w-1 h-1 rounded-full bg-red-500"></span>
                    </div>
                    <select
                        v-model="form.payment_day"
                        class="w-full px-3 py-3 sm:py-2.5 bg-gray-50 border-0 rounded-xl text-base sm:text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 appearance-none"
                    >
                        <option v-for="day in 31" :key="day" :value="day">{{ day }}{{ getDayOrdinal(day) }}</option>
                    </select>
                </div>
                <div>
                    <div class="flex items-center gap-1 mb-1.5">
                        <span class="text-xs text-gray-500">Start</span>
                        <span class="w-1 h-1 rounded-full bg-red-500"></span>
                    </div>
                    <input
                        v-model="form.first_payment_date"
                        type="date"
                        class="w-full px-3 py-3 sm:py-2.5 bg-gray-50 border-0 rounded-xl text-base sm:text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20"
                        required
                    />
                </div>
            </div>

            <!-- Optional fields -->
            <details class="group">
                <summary class="text-xs text-gray-400 cursor-pointer hover:text-gray-600 flex items-center gap-1 py-1">
                    <ChevronRight class="h-3 w-3 group-open:rotate-90 transition-transform" />
                    More options (optional)
                </summary>
                <div class="grid grid-cols-2 gap-2 mt-2">
                    <select
                        v-model="form.category_id"
                        class="w-full px-3 py-2.5 bg-gray-50 border-0 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 appearance-none"
                    >
                        <option :value="null">Category</option>
                        <option v-for="cat in expenseCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <select
                        v-if="wallets.length > 0"
                        v-model="form.wallet_id"
                        class="w-full px-3 py-2.5 bg-gray-50 border-0 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 appearance-none"
                    >
                        <option :value="null">Wallet</option>
                        <option v-for="wallet in wallets" :key="wallet.id" :value="wallet.id">{{ wallet.name }}</option>
                    </select>
                </div>
            </details>

            <!-- Actions - Large touch targets -->
            <div class="flex gap-2 pt-1">
                <button
                    v-if="editingExpense"
                    type="button"
                    @click="$emit('delete')"
                    :disabled="loading"
                    class="p-3 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 active:bg-red-200 transition-colors disabled:opacity-50"
                >
                    <Trash2 class="h-5 w-5" />
                </button>
                <button
                    type="submit"
                    :disabled="loading || !isFormValid"
                    class="flex-1 py-3.5 sm:py-3 px-4 rounded-xl text-base sm:text-sm font-semibold bg-gray-900 text-white hover:bg-gray-800 active:bg-gray-950 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <span v-if="loading" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        Saving...
                    </span>
                    <span v-else>{{ editingExpense ? 'Save Changes' : 'Add Expense' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import { X, Tv, Landmark, Receipt, MoreHorizontal, Trash2, ChevronRight, Zap, Wifi, Car, Home, Music, Cloud, Smartphone, CreditCard } from 'lucide-vue-next';
import dayjs from 'dayjs';
import { useSettingsStore } from '../store/settings';
import { useWalletStore } from '../store/wallet';
import { mapState } from 'pinia';

export default {
    name: 'RecurringExpenseForm',
    components: {
        X, Tv, Landmark, Receipt, MoreHorizontal, Trash2, ChevronRight, Zap, Wifi, Car, Home, Music, Cloud, Smartphone, CreditCard
    },
    props: {
        editingExpense: { type: Object, default: null },
        loading: { type: Boolean, default: false },
        suggestion: { type: Object, default: null }
    },
    emits: ['save', 'delete', 'cancel'],
    data() {
        return {
            form: {
                type: 'subscription',
                name: '',
                amount: '',
                payment_day: new Date().getDate(),
                first_payment_date: dayjs().format('YYYY-MM-DD'),
                recurrence: 'monthly',
                principal_amount: null,
                interest_rate: null,
                tenure_months: null,
                category_id: null,
                wallet_id: null
            },
            selectedPreset: null,
            emiDetails: null,
            typeConfig: {
                subscription: {
                    label: 'Subs',
                    icon: 'Tv',
                    selectedBg: 'bg-purple-100',
                    selectedText: 'text-purple-700',
                    ringColor: 'ring-purple-300'
                },
                bill: {
                    label: 'Bills',
                    icon: 'Receipt',
                    selectedBg: 'bg-green-100',
                    selectedText: 'text-green-700',
                    ringColor: 'ring-green-300'
                },
                emi: {
                    label: 'Loan',
                    icon: 'Landmark',
                    selectedBg: 'bg-amber-100',
                    selectedText: 'text-amber-700',
                    ringColor: 'ring-amber-300'
                },
                other: {
                    label: 'Other',
                    icon: 'MoreHorizontal',
                    selectedBg: 'bg-gray-200',
                    selectedText: 'text-gray-700',
                    ringColor: 'ring-gray-300'
                }
            },
            presets: [
                { name: 'Netflix', type: 'subscription', amount: 15.99 },
                { name: 'Spotify', type: 'subscription', amount: 9.99 },
                { name: 'YouTube', type: 'subscription', amount: 13.99 },
                { name: 'iCloud', type: 'subscription', amount: 2.99 },
                { name: 'Electricity', type: 'bill', amount: null },
                { name: 'Internet', type: 'bill', amount: null },
                { name: 'Phone', type: 'bill', amount: null },
                { name: 'Rent', type: 'bill', amount: null },
                { name: 'Car Loan', type: 'emi', amount: null },
                { name: 'Home Loan', type: 'emi', amount: null },
                { name: 'Personal Loan', type: 'emi', amount: null },
            ]
        };
    },
    computed: {
        ...mapState(useSettingsStore, ['categories', 'currencySymbol']),
        ...mapState(useWalletStore, ['wallets']),
        displayCurrencySymbol() {
            return this.currencySymbol || '$';
        },
        expenseCategories() {
            return this.categories?.filter(c => c.type === 'expense') || [];
        },
        filteredPresets() {
            return this.presets.filter(p => p.type === this.form.type);
        },
        namePlaceholder() {
            const placeholders = {
                subscription: 'e.g., Netflix, Spotify',
                emi: 'e.g., Car Loan',
                bill: 'e.g., Electricity',
                other: 'e.g., Gym'
            };
            return placeholders[this.form.type] || 'Name';
        },
        isFormValid() {
            if (!this.form.name || !this.form.amount || !this.form.payment_day) return false;
            if (this.form.type === 'emi' && (!this.form.principal_amount || !this.form.interest_rate || !this.form.tenure_months)) return false;
            return true;
        }
    },
    watch: {
        'form.type'(newType) {
            if (newType !== 'emi') {
                this.form.principal_amount = null;
                this.form.interest_rate = null;
                this.form.tenure_months = null;
                this.emiDetails = null;
            } else {
                this.form.recurrence = 'monthly';
            }
            this.selectedPreset = null;
        },
        editingExpense: {
            handler(newExpense) {
                if (newExpense) {
                    this.form = {
                        type: newExpense.type || 'subscription',
                        name: newExpense.name || '',
                        amount: newExpense.amount ? parseFloat(newExpense.amount) : '',
                        payment_day: newExpense.payment_day || new Date().getDate(),
                        first_payment_date: newExpense.first_payment_date ?
                            dayjs(newExpense.first_payment_date).format('YYYY-MM-DD') : dayjs().format('YYYY-MM-DD'),
                        recurrence: newExpense.recurrence || 'monthly',
                        principal_amount: newExpense.principal_amount ? parseFloat(newExpense.principal_amount) : null,
                        interest_rate: newExpense.interest_rate ? parseFloat(newExpense.interest_rate) : null,
                        tenure_months: newExpense.tenure_months || null,
                        category_id: newExpense.category_id || null,
                        wallet_id: newExpense.wallet_id || null
                    };
                    if (newExpense.type === 'emi') this.calculateEMI();
                } else {
                    this.resetForm();
                }
            },
            immediate: true
        },
        suggestion: {
            handler(s) {
                if (s) {
                    this.form = {
                        type: s.type || 'other',
                        name: s.name || '',
                        amount: s.amount || '',
                        payment_day: s.payment_day || new Date().getDate(),
                        first_payment_date: s.first_payment_date || dayjs().format('YYYY-MM-DD'),
                        recurrence: s.recurrence || 'monthly',
                        principal_amount: null,
                        interest_rate: null,
                        tenure_months: null,
                        category_id: s.category_id || null,
                        wallet_id: s.wallet_id || null
                    };
                }
            },
            immediate: true
        },
        'form.principal_amount': 'calculateEMI',
        'form.interest_rate': 'calculateEMI',
        'form.tenure_months': 'calculateEMI'
    },
    methods: {
        applyPreset(preset) {
            this.selectedPreset = preset.name;
            this.form.name = preset.name;
            this.form.type = preset.type;
            if (preset.amount) this.form.amount = preset.amount;
        },
        calculateEMI() {
            if (this.form.type !== 'emi' || !this.form.principal_amount || !this.form.interest_rate || !this.form.tenure_months) {
                this.emiDetails = null;
                return;
            }
            const p = this.form.principal_amount;
            const r = (this.form.interest_rate / 12) / 100;
            const n = this.form.tenure_months;

            if (r === 0) {
                this.form.amount = Math.round((p / n) * 100) / 100;
                this.emiDetails = { monthlyEMI: p / n, totalAmount: p, totalInterest: 0 };
                return;
            }

            const monthlyEMI = (p * r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
            const totalAmount = monthlyEMI * n;
            const totalInterest = totalAmount - p;

            this.form.amount = Math.round(monthlyEMI * 100) / 100;
            this.emiDetails = { monthlyEMI, totalAmount, totalInterest };
        },
        formatNumber(num) {
            if (!num) return '0';
            return new Intl.NumberFormat('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num);
        },
        getDayOrdinal(day) {
            if (day > 3 && day < 21) return 'th';
            switch (day % 10) {
                case 1: return 'st';
                case 2: return 'nd';
                case 3: return 'rd';
                default: return 'th';
            }
        },
        handleSubmit() {
            const data = { ...this.form, amount: parseFloat(this.form.amount) };
            if (this.form.type === 'emi' && this.emiDetails) {
                data.total_interest = this.emiDetails.totalInterest;
                data.end_date = dayjs(this.form.first_payment_date).add(this.form.tenure_months, 'month').format('YYYY-MM-DD');
            }
            this.$emit('save', data);
        },
        resetForm() {
            this.form = {
                type: 'subscription',
                name: '',
                amount: '',
                payment_day: new Date().getDate(),
                first_payment_date: dayjs().format('YYYY-MM-DD'),
                recurrence: 'monthly',
                principal_amount: null,
                interest_rate: null,
                tenure_months: null,
                category_id: null,
                wallet_id: null
            };
            this.selectedPreset = null;
            this.emiDetails = null;
        }
    }
};
</script>

<style scoped>
/* Prevent iOS zoom on input focus */
input, select {
    font-size: 16px;
}
@media (min-width: 640px) {
    input, select {
        font-size: 14px;
    }
}

/* Hide number spinners */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type=number] {
    -moz-appearance: textfield;
}

/* Date picker styling */
input[type="date"]::-webkit-calendar-picker-indicator {
    opacity: 0.5;
    cursor: pointer;
}

/* Select dropdown arrow */
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.25em 1.25em;
    padding-right: 2.5rem;
}

/* Hide scrollbar but allow scroll */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Safe area padding for iOS */
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 0);
}
</style>
