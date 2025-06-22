<!-- src/components/AddTransaction.vue -->
<template>
    <div class="p-3">

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

        <!-- Wallet Selection -->
        <div class="mb-3">
            <select v-model="wallet_id"
                class="w-full p-2.5 text-sm border rounded-md bg-gray-50 focus:bg-white
                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                required>
                <option value="" disabled>Select a wallet</option>
                <option v-for="wallet in wallets" :key="wallet.id" :value="wallet.id" class="py-1">
                    {{ wallet.name }} ({{ wallet.type }})
                </option>
            </select>
        </div>

        <!-- Enhanced Date Input -->
        <div class="grid grid-cols-1 gap-2 mb-3">
            <div>
                <CategorySearch v-model="category" :selectableCategory="editableCategory" :formType="type" />
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
            <div class="px-3 py-2 border-t border-gray-100">
                <div class="flex items-center gap-2">
                    <input type="text" v-model="reference_number" placeholder="Reference number (optional)"
                        class="w-full py-1 bg-transparent outline-none border-none focus:ring-0 focus:outline-none text-sm text-gray-700">
                    <div v-if="errors.reference_number" class="text-red-500 text-sm">
                        {{ errors.reference_number[0].message }}
                    </div>
                </div>
            </div>
        </div>


        <button v-if="isNew" @click="handleSubmit()" :disabled="!isValid || isSubmitting"
            class="w-full py-2.5 rounded-lg text-sm font-medium transition-all" :class="[
                isValid && !isSubmitting ? (
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
                <button @click="handleUpdate()" :disabled="!isValid || isSubmitting"
                    class="col-span-3 py-2.5 rounded-lg text-sm font-medium transition-all" :class="[
                        isValid && !isSubmitting ? (
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
import { useWalletStore } from '../store/wallet'
import { useTransactionStore } from '../store/transaction'
import { useNotifications } from '../composables/useNotifications'
import { onMounted } from 'vue'

export default {
    name: 'AddTransaction',
    mixins: [
        numberMixin
    ],
    setup() {
        const walletStore = useWalletStore()
        const transactionStore = useTransactionStore()
        const { notify } = useNotifications()
        
        onMounted(async () => {
            await walletStore.fetchWallets()
        })

        return {
            walletStore,
            transactionStore,
            notify
        }
    },
    data() {
        return {
            type: 'expense',
            amount: '',
            note: '',
            reference_number: '',
            category: '',
            editableCategory: '',
            date: this.getDate(),
            wallet_id: '',
            errors: {},
            isSubmitting: false
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
            return this.amount && this.amount > 0 && this.wallet_id
        },
        isNew() {
            return !this.item
        },
        selectedCategory() {
            if (!this.category || !this.categories) return null;
            return this.categories.find(cat => cat.id === this.category);
        },
        wallets() {
            return this.walletStore.wallets
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
                    this.wallet_id = newItem.wallet_id;
                    if (newItem.category) {
                        this.category = newItem.category.id;
                    }
                    this.reference_number = newItem.reference_number || '';
                } else {
                    this.type = 'expense';
                    this.amount = '';
                    this.note = '';
                    this.category = '';
                    this.date = this.getDate()
                    this.wallet_id = '';
                    this.reference_number = '';
                }
            }
        }
    },
    methods: {
        async handleSubmit() {
            if (!this.isValid) return;
            try {
                this.isSubmitting = true;
                this.errors = {};
                
                const transaction = {
                    type: this.type,
                    amount: parseFloat(this.amount),
                    note: this.note || '-',
                    reference_number: this.reference_number || null,
                    category_id: this.category,
                    transaction_date: this.formatDate(this.date),
                    wallet_id: this.wallet_id
                };
                
                // const result = await this.transactionStore.addTransaction(transaction);
                
                if (result.success) {
                    this.$emit('transaction-added', transaction);
                    this.resetForm();
                } else {
                    this.errors = result.errors;
                    console.log(this.errors)
                    // Display specific error for reference number if it exists
                    if (this.errors.reference_number) {
                        this.notify({
                            type: 'error',
                            message: this.errors.reference_number[0],
                        });
                    }
                    // Emit validation-error event to notify parent component
                    this.$emit('validation-error', this.errors);
                }
            } catch (error) {
                console.error(error);
                this.notify({
                    type: 'error',
                    message: 'An error occurred while saving the transaction.',
                    title: 'Error'
                });
                this.$emit('validation-error', { general: [{ message: 'An error occurred while saving the transaction.' }] });
            } finally {
                this.isSubmitting = false;
            }
        },
        async handleUpdate() {
            if (!this.isValid) return;
            try {
                this.isSubmitting = true;
                this.errors = {};
                
                const transaction = {
                    id: this.item.id,
                    type: this.type,
                    amount: parseFloat(this.amount),
                    note: this.note || '-',
                    reference_number: this.reference_number || null,
                    category_id: this.category,
                    transaction_date: this.formatDate(this.date),
                    wallet_id: this.wallet_id
                };
                
                const result = await this.transactionStore.updateTransaction(transaction);
                
                if (result.success) {
                    // this.$emit('transaction-updated', transaction);
                    this.resetForm();
                } else {
                    this.errors = result.errors;
                    // Display specific error for reference number if it exists
                    if (this.errors.reference_number) {
                        this.notify({
                            type: 'error',
                            message: this.errors.reference_number[0],
                        });
                    }
                    this.$emit('validation-error', this.errors);
                }
            } catch (error) {
                console.error(error);
                this.notify({
                    type: 'error',
                    message: 'A validation error occurred.',
                    title: 'Error'
                });
                this.$emit('validation-error', { general: [{ message: 'A validation error occurred.' }] });
            } finally {
                this.isSubmitting = false;
            }
        },
        resetForm() {
            this.amount = '';
            this.note = '';
            this.reference_number = '';
            this.category = '';
            this.date = this.getDate();
            this.wallet_id = '';
            this.$emit('close')
        },
    },
    async created() {
        await this.walletStore.fetchWallets()
        if (this.item) {
            this.amount = this.item.amount;
            this.note = this.item.note;
            this.reference_number = this.item.reference_number || '';
            this.type = this.item.type;
            this.date = this.formatDate(this.item.transaction_date);
            this.wallet_id = this.item.wallet_id;
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