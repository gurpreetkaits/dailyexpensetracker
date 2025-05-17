<template>
    <div class="space-y-6">


        <form @submit.prevent="handleSubmit" class="grid grid-cols-2 gap-4 lg:px-2 lg:py-3">
            <!-- Expense Type -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    Type
                </label>
                <div class="border border-gray-100 rounded-md hover:bg-gray-50">
                    <select v-model="form.type" class="w-full px-3 py-1 bg-transparent text-gray-800
                               border border-transparent rounded-md focus:bg-white
                               text-sm focus:ring-0 transition-colors" required>
                        <option value="subscription">Subscription</option>
                        <option value="emi">EMI/Loan</option>
                        <option value="bill">Bill</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <!-- Name -->
            <div style="margin: 0;">
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    Name
                </label>
                <div class="border border-gray-100 rounded-md hover:bg-gray-50">
                    <input v-model="form.name" type="text" class="w-full px-3 py-1 bg-transparent text-gray-800
                               border border-transparent rounded-md focus:bg-white
                               text-sm focus:ring-0 transition-colors"
                           :placeholder="form.type === 'emi' ? 'e.g., Car Loan' : 'e.g., Netflix'" required />
                </div>
            </div>

            <!-- EMI Specific Fields -->
            <template v-if="form.type === 'emi'">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        Principal Amount
                    </label>
                    <div class="border border-gray-100 rounded-md hover:bg-gray-50">
                        <div class="flex items-center">
                            <span class="text-sm px-2 text-gray-400">{{ currencySymbol }}</span>
                            <input v-model.number="form.principal_amount" type="number" class="w-full py-1 px-2 bg-transparent text-gray-800
                                       border border-transparent rounded-md focus:bg-white
                                       text-sm focus:ring-0 transition-colors" placeholder="0.00" required />
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        Annual Interest Rate (%)
                    </label>
                    <div class="border border-gray-100 rounded-md hover:bg-gray-50">
                        <input v-model.number="form.interest_rate" type="number" step="0.01" class="w-full px-3 py-1 bg-transparent text-gray-800
                                   border border-transparent rounded-md focus:bg-white
                                   text-sm focus:ring-0 transition-colors" placeholder="e.g., 12.5" required />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        Tenure (Months)
                    </label>
                    <div class="border border-gray-100 rounded-md hover:bg-gray-50">
                        <input v-model.number="form.tenure_months" type="number" class="w-full px-3 py-1 bg-transparent text-gray-800
                                   border border-transparent rounded-md focus:bg-white
                                   text-sm focus:ring-0 transition-colors" placeholder="e.g., 36" required />
                    </div>
                </div>
            </template>

            <!-- Common Fields -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    {{ form.type === 'emi' ? 'Monthly EMI' : 'Amount' }}
                </label>
                <div class="border border-gray-100 rounded-md hover:bg-gray-50">
                    <div class="flex items-center">
                        <span class="text-sm px-2 text-gray-400">{{ currencySymbol }}</span>
                        <input v-model.number="form.amount" type="text" class="w-full py-1 px-2 bg-transparent text-gray-800
                                   border border-transparent rounded-md focus:bg-white
                                   text-sm focus:ring-0 transition-colors" placeholder="0.00" required />
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    Payment Day
                </label>
                <div class="border border-gray-100 rounded-md hover:bg-gray-50">
                    <select v-model="form.payment_day" class="w-full px-3 py-1 bg-transparent text-gray-800
                               border border-transparent rounded-md focus:bg-white
                               text-sm focus:ring-0 transition-colors" required>
                        <option value="" disabled>Select day</option>
                        <option v-for="day in 31" :key="day" :value="day">
                            {{ day }}{{ getDayOrdinal(day) }}
                        </option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    First Payment Date
                </label>
                <div class="border border-gray-100 rounded-md hover:bg-gray-50">
                    <input v-model="form.first_payment_date" type="date" class="w-full px-3 py-1 bg-transparent text-gray-800
                               border border-transparent rounded-md focus:bg-white
                               text-sm focus:ring-0 transition-colors" required />
                </div>
            </div>

            <div class="">
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    Recurrence
                </label>
                <div class="border border-gray-100 rounded-md hover:bg-gray-50">
                    <select v-model="form.recurrence" class="w-full px-3 py-1 bg-transparent text-gray-800
                               border border-transparent rounded-md focus:bg-white
                               text-sm focus:ring-0 transition-colors" :disabled="form.type === 'emi'" required>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" :disabled="loading" class="w-full col-span-2 bg-blue-100 text-blue-600 py-2 px-3 rounded-md
                       hover:bg-gray-200 transition-colors text-sm
                       disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="!loading">
                    {{ editingExpense ? 'Update' : 'Create' }} {{ form.type === 'emi' ? 'EMI' : 'Expense' }}
                </span>
                <span v-else class="flex items-center justify-center">
                    <svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                                fill="none" />
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    Processing...
                </span>
            </button>

            <button v-if="editingExpense" type="button" :disabled="loading" @click="$emit('delete')"
                    class="col-span-2 w-full border py-2 px-3 rounded-md bg-red-50 text-red-500 border-red-100
                           transition-colors text-sm disabled:opacity-50">
                Delete Expense
            </button>
        </form>
    </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import { X } from 'lucide-vue-next';
import dayjs from 'dayjs';

export default {
    name: 'ExpenseForm',
    components: {
        X
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
        currencySymbol: {
            type: String,
            default: '$'
        }
    },
    emits: ['save', 'delete', 'cancel'],
    data() {
        return {
            form: {
                type: 'subscription',
                name: '',
                amount: '',
                payment_day: '',
                first_payment_date: '',
                recurrence: 'monthly',
                principal_amount: null,
                interest_rate: null,
                tenure_months: null,
                totalInterest: null,
            },
            tomorrow: this.getTomorrow(),
            emiDetails: null
        };
    },
    watch: {
        'form.type': {
            handler(newType) {
                if (newType !== 'emi') {
                    this.form.principal_amount = null;
                    this.form.interest_rate = null;
                    this.form.tenure_months = null;
                    this.form.amount = '';
                }
                if (newType === 'emi') {
                    this.form.recurrence = 'monthly';
                }
            }
        },
        editingExpense: {
            handler(newExpense) {
                if (newExpense) {
                    this.form = {
                        type: newExpense.type || 'subscription',
                        name: newExpense.name || '',
                        amount: newExpense.amount ? parseFloat(newExpense.amount) : '',
                        payment_day: newExpense.payment_day || '',
                        first_payment_date: newExpense.first_payment_date ?
                            dayjs(newExpense.first_payment_date).format('YYYY-MM-DD') : '',
                        recurrence: newExpense.recurrence || 'monthly',
                        principal_amount: newExpense.principal_amount ?
                            parseFloat(newExpense.principal_amount) : null,
                        interest_rate: newExpense.interest_rate ?
                            parseFloat(newExpense.interest_rate) : null,
                        tenure_months: newExpense.tenure_months || null,
                        totalInterest: newExpense.total_interest || null
                    };
                } else {
                    this.resetForm();
                }
            },
            immediate: true
        },
        'form.principal_amount': 'calculateEMI',
        'form.interest_rate': 'calculateEMI',
        'form.tenure_months': 'calculateEMI'
    },
    methods: {
        getTomorrow() {
            const date = new Date();
            date.setDate(date.getDate() + 1);
            return date.toISOString().split('T')[0];
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

            const monthlyEMI = (p * r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
            const totalAmount = monthlyEMI * n;
            const totalInterest = totalAmount - p;

            this.form.amount = monthlyEMI;
            this.emiDetails = {
                monthlyEMI,
                totalAmount,
                totalInterest
            };
        },
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
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
                payment_day: '',
                first_payment_date: '',
                recurrence: 'monthly',
                principal_amount: null,
                interest_rate: null,
                tenure_months: null,
                totalInterest: null
            };
        }
    }
};
</script>

<style scoped>
/* Remove spinners from number input */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
}

/* Custom date picker styling */
input[type="date"]::-webkit-calendar-picker-indicator {
    opacity: 0.5;
}
</style>
