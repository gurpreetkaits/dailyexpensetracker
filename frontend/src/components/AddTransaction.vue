<!-- src/components/AddTransaction.vue -->
<template>
    <div class="p-3">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900">
                {{ isNew ? 'New Transaction' : 'Edit Transaction' }}
            </h2>
            <button @click="$emit('close')" class="text-gray-400 hover:text-gray-500 border px-2 rounded-lg">
                Close
            </button>
        </div>
        <!-- Type Toggle -->
        <div class="flex gap-2 mb-4">
            <button @click="type = 'expense'"
                class="flex-1 py-2 px-3 rounded-lg flex items-center justify-center gap-1.5 text-sm transition-all"
                :class="type === 'expense'
                    ? 'bg-red-50 text-red-500'
                    : 'bg-gray-50 text-gray-500'">
                <div>
                    <ShoppingBag size="15"></ShoppingBag>
                </div>
                <div>
                    Expense
                </div>

            </button>
            <button @click="type = 'income'"
                class="flex-1 py-2 px-3 rounded-lg flex items-center justify-center gap-1.5 text-sm transition-all"
                :class="type === 'income'
                    ? 'bg-green-50 text-green-500'
                    : 'bg-gray-50 text-gray-500'">
                <div>
                    <Wallet size="15"></Wallet>
                </div>
                <div>
                    Income
                </div>
            </button>
        </div>
        <!-- Enhanced Date Input -->
        <div class="grid grid-cols-2 gap-2">
            <div>
                <CategorySearch v-model="category" :selectableCategory="editableCategory" :formType="type" />
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600">Transaction Date</label>
                <div class="relative">
                    <input v-model="date" type="date"
                        class="w-full p-2.5 pl-9 text-sm focus:outline-none focus:ring-0 border border-gray-200 rounded-md bg-gray-50 focus:bg-white transition-colors mb-2"
                        required />
                </div>
            </div>

        </div>
        <!-- Amount Input -->
        <div class="bg-gray-50 rounded-lg mb-3">
            <div class="p-4">
                <div class="flex items-center justify-center">
                    <!-- <span class="text-lg" :class="type === 'expense' ? 'text-red-500' : 'text-green-500'">₹</span> -->
                    <input type="number" v-model="amount" placeholder="0"
                        class="text-34 w-38 text-center bg-transparent focus:outline-none focus:ring-0 border-none">
                </div>
            </div>
            <div class="px-3 py-2">
                <div class="flex items-center gap-2">
                    <input type="text" v-model="note" placeholder="Add a note..."
                        class="w-full py-1 bg-transparent outline-none border-none  focus:ring-0 focus:outline-none text-sm text-gray-700">
                </div>
            </div>
        </div>


        <button v-if="isNew" @click="handleSubmit()" :disabled="!isValid"
            class="w-full py-2.5 rounded-lg text-sm font-medium transition-all" :class="[
                isValid ? (
                    type === 'expense'
                        ? 'bg-red-500 hover:bg-red-600 text-white'
                        : 'bg-green-500 hover:bg-green-600 text-white'
                ) : 'bg-gray-100 text-gray-400 cursor-not-allowed'
            ]">
            Add {{ type }}
        </button>
        <!-- Handle Update -->
        <template v-else>
            <div class="grid grid-cols-4 gap-2">
                <button @click="handleUpdate()" :disabled="!isValid"
                    class="col-span-3 py-2.5 rounded-lg text-sm font-medium transition-all" :class="[
                        isValid ? (
                            type === 'expense'
                                ? 'bg-red-500 hover:bg-red-600 text-white'
                                : 'bg-green-500 hover:bg-green-600 text-white'
                        ) : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                    ]">
                    Update {{ type }}
                </button>

                <button @click="$emit('remove', item.id)"
                    class="col-span-1 py-2.5 rounded-lg text-sm font-medium bg-red-50 text-red-500">
                    <Trash2 class="h-4 w-4 mx-auto" />
                </button>
            </div>
        </template>
    </div>
</template>

<script>
import { ShoppingBag, Wallet, Trash2, CircleX } from 'lucide-vue-next';
import CategorySearch from './CategorySearch.vue'
import dayjs from 'dayjs'
import { numberMixin } from '../mixins/numberMixin.js'

export default {
    name: 'AddTransaction',
    mixins: [
        numberMixin
    ],
    data() {
        return {
            type: 'expense',
            amount: '',
            note: '',
            category: '',
            editableCategory: '',
            date: this.getDate()
        }
    },
    props: {
        item: {
            type: Object,
            default: null
        }
    },
    components: {
        ShoppingBag, Wallet, Trash2, CategorySearch, CircleX
    },
    computed: {
        isValid() {
            return this.amount && this.amount > 0
        },
        isNew() {
            return !this.item
        },
        selectedCategory() {
            if (!this.category || !this.categories) return null;
            return this.categories.find(cat => cat.id === this.category);
        }
    },
    watch: {
        item: {
            immediate: true,
            handler(newItem) {
                console.log('newItem', newItem)
                if (newItem) {
                    this.type = newItem.type;
                    this.amount = newItem.amount;
                    this.note = newItem.note;
                    this.date = newItem.transaction_date ? this.formatDate(newItem.transaction_date) : this.date;
                    if (newItem.category) {
                        this.category = newItem.category.id;
                    }
                } else {
                    this.type = 'expense';
                    this.amount = '';
                    this.note = '';
                    this.category = '';
                    this.date = this.getDate()
                }
            }
        }
    },
    methods: {
        async handleSubmit() {
            if (!this.isValid) return;
            try {
                const transaction = {
                    type: this.type,
                    amount: parseFloat(this.amount),
                    note: this.note || '-',
                    category_id: this.category,
                    transaction_date: this.formatDate(this.date)
                };
                this.$emit('transaction-added', transaction);
                // Reset form
                this.resetForm();
            } catch (error) {
                console.error(error);
            }
        },
        async handleUpdate() {
            if (!this.isValid) return;
            try {
                const transaction = {
                    type: this.type,
                    amount: parseFloat(this.amount),
                    note: this.note || (this.type === 'income' ? 'Income' : 'Expense'),
                    category_id: this.category,
                    transaction_date: this.formatDate(this.date)
                };
                this.$emit('transaction-added', transaction);
                this.resetForm();
            } catch (error) {
                console.error(error);
            }
        },
        resetForm() {
            this.amount = '';
            this.note = '';
            this.category = '';
            this.date = this.getDate()
        },
    },
    async created() {
        if (this.item) {
            this.amount = this.item.amount;
            this.note = this.item.note;
            this.type = this.item.type;
            this.date = this.formatDate(this.item.transaction_date);
            if (this.item.category) {
                this.category = this.item.category?.id;
                this.editableCategory = this.item.category
            }
        }
    },
}
</script>

<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: 100%;
    height: 100%;
}
</style>