<template>
    <div class="space-y-5">
        <!-- Header with Status -->
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ editingExpense ? 'Edit Recurring Expense' : 'Add Recurring Expense' }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ editingExpense ? 'Update your recurring payment details' : 'Track subscriptions, bills, and EMIs' }}
                </p>
            </div>
            <button @click="$emit('cancel')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                <X class="h-5 w-5" />
            </button>
        </div>

        <!-- Edit Mode Status Card -->
        <div v-if="editingExpense" class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center"
                         :class="typeConfig[form.type]?.bgClass || 'bg-gray-100'">
                        <component :is="typeConfig[form.type]?.icon || 'RepeatIcon'"
                                   class="h-5 w-5"
                                   :class="typeConfig[form.type]?.iconClass || 'text-gray-600'" />
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ form.name || 'Untitled' }}</p>
                        <p class="text-xs text-gray-500">{{ displayCurrencySymbol }}{{ formatNumber(form.amount) }}/{{ form.recurrence }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                          :class="editingExpense.is_active !== false ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                        {{ editingExpense.is_active !== false ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-5">
            <!-- Type Selection Cards -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-2">Expense Type</label>
                <div class="grid grid-cols-4 gap-2">
                    <button
                        v-for="(config, type) in typeConfig"
                        :key="type"
                        type="button"
                        @click="form.type = type"
                        class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition-all"
                        :class="form.type === type
                            ? `${config.selectedBg} ${config.selectedBorder} ${config.selectedText}`
                            : 'border-gray-100 bg-white hover:border-gray-200 hover:bg-gray-50 text-gray-600'"
                    >
                        <component :is="config.icon" class="h-5 w-5" />
                        <span class="text-xs font-medium">{{ config.label }}</span>
                    </button>
                </div>
            </div>

            <!-- Quick Presets (New Mode Only) -->
            <div v-if="!editingExpense && filteredPresets.length > 0" class="space-y-2">
                <label class="block text-xs font-medium text-gray-600">Quick Add</label>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="preset in filteredPresets"
                        :key="preset.name"
                        type="button"
                        @click="applyPreset(preset)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-full border transition-all"
                        :class="selectedPreset === preset.name
                            ? 'bg-gray-900 border-gray-900 text-white'
                            : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50'"
                    >
                        <component :is="preset.icon" class="h-3 w-3" />
                        {{ preset.name }}
                    </button>
                </div>
            </div>

            <!-- Name Input -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Name</label>
                <input
                    v-model="form.name"
                    type="text"
                    class="w-full px-4 py-2.5 bg-gray-50 border-0 rounded-xl text-gray-900 text-sm placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all"
                    :placeholder="namePlaceholder"
                    required
                />
            </div>

            <!-- EMI Specific Fields -->
            <div v-if="form.type === 'emi'" class="space-y-4">
                <div class="bg-amber-50 rounded-xl p-4 border border-amber-100">
                    <h4 class="text-xs font-medium text-amber-800 mb-3 flex items-center gap-1.5">
                        <Calculator class="h-3.5 w-3.5" />
                        Loan Details
                    </h4>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-amber-700 mb-1">Principal</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-amber-600">{{ displayCurrencySymbol }}</span>
                                <input
                                    v-model.number="form.principal_amount"
                                    type="number"
                                    class="w-full pl-7 pr-3 py-2 bg-white border border-amber-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-300"
                                    placeholder="0"
                                    required
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-amber-700 mb-1">Interest %</label>
                            <input
                                v-model.number="form.interest_rate"
                                type="number"
                                step="0.01"
                                class="w-full px-3 py-2 bg-white border border-amber-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-300"
                                placeholder="12.5"
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-xs text-amber-700 mb-1">Tenure</label>
                            <div class="relative">
                                <input
                                    v-model.number="form.tenure_months"
                                    type="number"
                                    class="w-full px-3 pr-12 py-2 bg-white border border-amber-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-300"
                                    placeholder="36"
                                    required
                                />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-amber-600">months</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EMI Result Card -->
                <div v-if="emiDetails" class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl p-4 text-white">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-medium text-amber-100">Monthly EMI</span>
                        <span class="text-2xl font-bold">{{ displayCurrencySymbol }}{{ formatNumber(emiDetails.monthlyEMI) }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-3 border-t border-amber-400/30">
                        <div>
                            <p class="text-xs text-amber-100">Total Interest</p>
                            <p class="text-sm font-semibold">{{ displayCurrencySymbol }}{{ formatNumber(emiDetails.totalInterest) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-amber-100">Total Payable</p>
                            <p class="text-sm font-semibold">{{ displayCurrencySymbol }}{{ formatNumber(emiDetails.totalAmount) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amount & Frequency Row -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        {{ form.type === 'emi' ? 'EMI Amount' : 'Amount' }}
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-400">{{ displayCurrencySymbol }}</span>
                        <input
                            v-model.number="form.amount"
                            type="number"
                            step="0.01"
                            class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border-0 rounded-xl text-gray-900 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all"
                            placeholder="0.00"
                            :readonly="form.type === 'emi'"
                            :class="form.type === 'emi' ? 'cursor-not-allowed opacity-75' : ''"
                            required
                        />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Frequency</label>
                    <select
                        v-model="form.recurrence"
                        class="w-full px-4 py-2.5 bg-gray-50 border-0 rounded-xl text-gray-900 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all appearance-none cursor-pointer"
                        :disabled="form.type === 'emi'"
                        required
                    >
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
            </div>

            <!-- Payment Day & Start Date Row -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Payment Day</label>
                    <select
                        v-model="form.payment_day"
                        class="w-full px-4 py-2.5 bg-gray-50 border-0 rounded-xl text-gray-900 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all appearance-none cursor-pointer"
                        required
                    >
                        <option v-for="day in 31" :key="day" :value="day">
                            {{ day }}{{ getDayOrdinal(day) }} of month
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Start Date</label>
                    <input
                        v-model="form.first_payment_date"
                        type="date"
                        class="w-full px-4 py-2.5 bg-gray-50 border-0 rounded-xl text-gray-900 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all"
                        required
                    />
                </div>
            </div>

            <!-- Category & Wallet Row -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Category</label>
                    <select
                        v-model="form.category_id"
                        class="w-full px-4 py-2.5 bg-gray-50 border-0 rounded-xl text-gray-900 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all appearance-none cursor-pointer"
                    >
                        <option :value="null">No category</option>
                        <option v-for="cat in expenseCategories" :key="cat.id" :value="cat.id">
                            {{ cat.name }}
                        </option>
                    </select>
                </div>
                <div v-if="wallets.length > 0">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Wallet</label>
                    <select
                        v-model="form.wallet_id"
                        class="w-full px-4 py-2.5 bg-gray-50 border-0 rounded-xl text-gray-900 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all appearance-none cursor-pointer"
                    >
                        <option :value="null">No wallet</option>
                        <option v-for="wallet in wallets" :key="wallet.id" :value="wallet.id">
                            {{ wallet.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Cost Summary Card -->
            <div v-if="form.amount" class="bg-gray-50 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm">
                            <TrendingUp class="h-4 w-4 text-gray-600" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Yearly Cost</p>
                            <p class="text-lg font-semibold text-gray-900">{{ displayCurrencySymbol }}{{ formatNumber(yearlyCost) }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">{{ form.recurrence === 'monthly' ? '12 payments' : form.recurrence === 'quarterly' ? '4 payments' : '1 payment' }}/year</p>
                        <p class="text-sm font-medium text-gray-700">{{ displayCurrencySymbol }}{{ formatNumber(form.amount) }} each</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2 pt-2">
                <button
                    type="submit"
                    :disabled="loading || !isFormValid"
                    class="w-full py-3 px-4 rounded-xl text-sm font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="editingExpense
                        ? 'bg-blue-600 hover:bg-blue-700 text-white'
                        : 'bg-gray-900 hover:bg-gray-800 text-white'"
                >
                    <span v-if="!loading" class="flex items-center justify-center gap-2">
                        <component :is="editingExpense ? 'Check' : 'Plus'" class="h-4 w-4" />
                        {{ editingExpense ? 'Save Changes' : 'Add Expense' }}
                    </span>
                    <span v-else class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        {{ editingExpense ? 'Saving...' : 'Adding...' }}
                    </span>
                </button>

                <button
                    v-if="editingExpense"
                    type="button"
                    :disabled="loading"
                    @click="$emit('delete')"
                    class="w-full py-2.5 px-4 rounded-xl text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 transition-all disabled:opacity-50"
                >
                    <span class="flex items-center justify-center gap-2">
                        <Trash2 class="h-4 w-4" />
                        Delete Expense
                    </span>
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import { X, Tv, Zap, Wifi, Car, Home, CreditCard, Music, Cloud, Smartphone, RepeatIcon, Calculator, TrendingUp, Plus, Check, Trash2, Landmark, Receipt, MoreHorizontal } from 'lucide-vue-next';
import dayjs from 'dayjs';
import { useSettingsStore } from '../store/settings';
import { useWalletStore } from '../store/wallet';
import { mapState } from 'pinia';

export default {
    name: 'RecurringExpenseForm',
    components: {
        X, Tv, Zap, Wifi, Car, Home, CreditCard, Music, Cloud, Smartphone, RepeatIcon, Calculator, TrendingUp, Plus, Check, Trash2, Landmark, Receipt, MoreHorizontal
    },
    props: {
        editingExpense: {
            type: Object,
            default: null
        },
        loading: {
            type: Boolean,
            default: false
        },
        suggestion: {
            type: Object,
            default: null
        }
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
                    label: 'Subscription',
                    icon: 'Tv',
                    bgClass: 'bg-purple-100',
                    iconClass: 'text-purple-600',
                    selectedBg: 'bg-purple-50',
                    selectedBorder: 'border-purple-500',
                    selectedText: 'text-purple-700'
                },
                bill: {
                    label: 'Bill',
                    icon: 'Receipt',
                    bgClass: 'bg-green-100',
                    iconClass: 'text-green-600',
                    selectedBg: 'bg-green-50',
                    selectedBorder: 'border-green-500',
                    selectedText: 'text-green-700'
                },
                emi: {
                    label: 'EMI/Loan',
                    icon: 'Landmark',
                    bgClass: 'bg-amber-100',
                    iconClass: 'text-amber-600',
                    selectedBg: 'bg-amber-50',
                    selectedBorder: 'border-amber-500',
                    selectedText: 'text-amber-700'
                },
                other: {
                    label: 'Other',
                    icon: 'MoreHorizontal',
                    bgClass: 'bg-gray-100',
                    iconClass: 'text-gray-600',
                    selectedBg: 'bg-gray-100',
                    selectedBorder: 'border-gray-500',
                    selectedText: 'text-gray-700'
                }
            },
            presets: [
                { name: 'Netflix', type: 'subscription', icon: 'Tv', amount: 15.99 },
                { name: 'Spotify', type: 'subscription', icon: 'Music', amount: 9.99 },
                { name: 'YouTube', type: 'subscription', icon: 'Tv', amount: 13.99 },
                { name: 'iCloud', type: 'subscription', icon: 'Cloud', amount: 2.99 },
                { name: 'Electricity', type: 'bill', icon: 'Zap', amount: null },
                { name: 'Internet', type: 'bill', icon: 'Wifi', amount: null },
                { name: 'Phone', type: 'bill', icon: 'Smartphone', amount: null },
                { name: 'Rent', type: 'bill', icon: 'Home', amount: null },
                { name: 'Car Loan', type: 'emi', icon: 'Car', amount: null },
                { name: 'Home Loan', type: 'emi', icon: 'Home', amount: null },
                { name: 'Personal Loan', type: 'emi', icon: 'CreditCard', amount: null },
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
                subscription: 'e.g., Netflix, Spotify, Gym',
                emi: 'e.g., Car Loan, Home Loan',
                bill: 'e.g., Electricity, Internet',
                other: 'e.g., Gym Membership'
            };
            return placeholders[this.form.type] || 'Enter name';
        },
        yearlyCost() {
            if (!this.form.amount) return 0;
            const multiplier = {
                monthly: 12,
                quarterly: 4,
                yearly: 1
            };
            return (this.form.amount * (multiplier[this.form.recurrence] || 12));
        },
        isFormValid() {
            if (!this.form.name || !this.form.amount || !this.form.payment_day) return false;
            if (this.form.type === 'emi' && (!this.form.principal_amount || !this.form.interest_rate || !this.form.tenure_months)) return false;
            return true;
        }
    },
    watch: {
        'form.type': {
            handler(newType) {
                if (newType !== 'emi') {
                    this.form.principal_amount = null;
                    this.form.interest_rate = null;
                    this.form.tenure_months = null;
                    this.emiDetails = null;
                }
                if (newType === 'emi') {
                    this.form.recurrence = 'monthly';
                }
                this.selectedPreset = null;
            }
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
                        principal_amount: newExpense.principal_amount ?
                            parseFloat(newExpense.principal_amount) : null,
                        interest_rate: newExpense.interest_rate ?
                            parseFloat(newExpense.interest_rate) : null,
                        tenure_months: newExpense.tenure_months || null,
                        category_id: newExpense.category_id || null,
                        wallet_id: newExpense.wallet_id || null
                    };
                    if (newExpense.type === 'emi') {
                        this.calculateEMI();
                    }
                } else {
                    this.resetForm();
                }
            },
            immediate: true
        },
        suggestion: {
            handler(newSuggestion) {
                if (newSuggestion) {
                    this.applySuggestion(newSuggestion);
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
            if (preset.amount) {
                this.form.amount = preset.amount;
            }
        },
        applySuggestion(suggestion) {
            this.form = {
                type: suggestion.type || 'other',
                name: suggestion.name || '',
                amount: suggestion.amount || '',
                payment_day: suggestion.payment_day || new Date().getDate(),
                first_payment_date: suggestion.first_payment_date || dayjs().format('YYYY-MM-DD'),
                recurrence: suggestion.recurrence || 'monthly',
                principal_amount: null,
                interest_rate: null,
                tenure_months: null,
                category_id: suggestion.category_id || null,
                wallet_id: suggestion.wallet_id || null
            };
        },
        calculateEMI() {
            if (this.form.type !== 'emi' ||
                !this.form.principal_amount ||
                !this.form.interest_rate ||
                !this.form.tenure_months) {
                this.emiDetails = null;
                return;
            }

            const p = this.form.principal_amount;
            const r = (this.form.interest_rate / 12) / 100;
            const n = this.form.tenure_months;

            if (r === 0) {
                this.form.amount = Math.round((p / n) * 100) / 100;
                this.emiDetails = {
                    monthlyEMI: p / n,
                    totalAmount: p,
                    totalInterest: 0
                };
                return;
            }

            const monthlyEMI = (p * r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
            const totalAmount = monthlyEMI * n;
            const totalInterest = totalAmount - p;

            this.form.amount = Math.round(monthlyEMI * 100) / 100;
            this.emiDetails = {
                monthlyEMI,
                totalAmount,
                totalInterest
            };
        },
        formatNumber(num) {
            if (!num) return '0';
            return new Intl.NumberFormat('en-IN', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(num);
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
            const expenseData = {
                ...this.form,
                amount: parseFloat(this.form.amount)
            };

            if (this.form.type === 'emi' && this.emiDetails) {
                expenseData.total_interest = this.emiDetails.totalInterest;
                expenseData.end_date = dayjs(this.form.first_payment_date)
                    .add(this.form.tenure_months, 'month')
                    .format('YYYY-MM-DD');
            }

            this.$emit('save', expenseData);
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
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    opacity: 0.5;
    cursor: pointer;
}

select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.25em 1.25em;
    padding-right: 2.5rem;
}
</style>
