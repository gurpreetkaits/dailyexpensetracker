<template>
    <div v-if="isVisible" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg w-full max-w-sm shadow-xl">
        <!-- Header -->
        <div class="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-lg">
          <h2 class="text-base font-semibold text-gray-700">Add New Transaction</h2>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100 transition-colors"
          >
            <X class="h-4 w-4" />
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="p-4 space-y-4">
          <!-- Type Selection -->
          <div class="space-y-1">
            <label class="text-xs font-medium text-gray-600">Type</label>
            <div class="grid grid-cols-2 gap-2">
              <button
                type="button"
                @click="form.type = 'expense'"
                class="p-2 text-sm rounded-md transition-colors"
                :class="form.type === 'expense' ? 'bg-red-50 text-red-600' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
              >
                Expense
              </button>
              <button
                type="button"
                @click="form.type = 'income'"
                class="p-2 text-sm rounded-md transition-colors"
                :class="form.type === 'income' ? 'bg-green-50 text-green-600' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
              >
                Income
              </button>
            </div>
          </div>

          <!-- Wallet Selection -->
          <div class="space-y-1">
            <label class="text-xs font-medium text-gray-600">Wallet</label>
            <select
              v-model="form.wallet_id"
              class="w-full p-2.5 text-sm border rounded-md bg-gray-50 focus:bg-white
                     focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              required
            >
              <option value="" disabled>Select a wallet</option>
              <option
                v-for="wallet in wallets"
                :key="wallet.id"
                :value="wallet.id"
                class="py-1"
              >
                {{ wallet.name }} ({{ wallet.type }})
              </option>
            </select>
          </div>

          <!-- Amount Input -->
          <div class="space-y-1">
            <label class="text-xs font-medium text-gray-600">Amount</label>
            <div class="relative">
              <DollarSign class="absolute left-2.5 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="form.amount"
                type="number"
                step="0.01"
                class="w-full p-2.5 pl-9 text-sm border rounded-md bg-gray-50 focus:bg-white
                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                placeholder="0.00"
                required
              />
            </div>
          </div>

          <!-- Category Select -->
          <div class="space-y-1">
            <label class="text-xs font-medium text-gray-600">Category</label>
            <select
              v-model="form.category"
              class="w-full p-2.5 text-sm border rounded-md bg-gray-50 focus:bg-white
                     focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              required
            >
              <option value="" disabled>Select a category</option>
                <option
                  v-for="category in filteredCategories"
                  :key="category.id"
                  :value="category.id"
                  class="py-1"
                >
                  {{ category.name }}
                </option>
            </select>
          </div>

          <!-- Note Input -->
          <div class="space-y-1">
            <label class="text-xs font-medium text-gray-600">Note (Optional)</label>
            <input
              v-model="form.note"
              type="text"
              class="w-full p-2.5 text-sm border rounded-md bg-gray-50 focus:bg-white
                     focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              placeholder="Add a note..."
            />
          </div>

          <!-- Date Input -->
          <div class="space-y-1">
            <label class="text-xs font-medium text-gray-600">Date</label>
            <input
              v-model="form.date"
              type="date"
              class="w-full p-2.5 text-sm border rounded-md bg-gray-50 focus:bg-white
                     focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              required
            />
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3 pt-2">
            <button
              type="button"
              @click="$emit('close')"
              class="flex-1 py-2.5 px-4 text-sm border rounded-md hover:bg-gray-50
                     focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500
                     transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="flex-1 py-2.5 px-4 text-sm bg-emerald-500 text-white rounded-md
                     hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500
                     focus:ring-offset-2 transition-colors"
            >
              Save Transaction
            </button>
          </div>
        </form>
      </div>
    </div>
  </template>

  <script setup>
  import { reactive, computed, onMounted } from 'vue'
  import { X, DollarSign } from 'lucide-vue-next'
  import { useWalletStore } from '../store/wallet'

  const props = defineProps({
    isVisible: Boolean,
    categories: {
      type: Array,
      required: true
    }
  })

  const emit = defineEmits(['close', 'save'])
  const walletStore = useWalletStore()

  const form = reactive({
    type: 'expense',
    amount: '',
    category: '',
    note: '',
    date: new Date().toISOString().split('T')[0],
    wallet_id: ''
  })

  const wallets = computed(() => walletStore.wallets)

  onMounted(async () => {
    await walletStore.fetchWallets()
  })

  const filteredCategories = computed(() => {
    return props.categories.filter(cat => cat.type.toLowerCase() === form.type.toLowerCase())
  })

  const handleSubmit = () => {
    emit('save', {
      ...form,
      amount: parseFloat(form.amount),
      transaction_date: form.date
    })
    // Reset form
    Object.assign(form, {
      type: 'expense',
      amount: '',
      category: '',
      note: '',
      date: new Date().toISOString().split('T')[0],
      wallet_id: ''
    })
  }
  </script>

  <style scoped>
  input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    padding: 4px;
    margin-right: -4px;
    opacity: 0.6;
  }

  input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
  }

  optgroup {
    font-weight: 600;
    color: #374151;
  }

  optgroup option {
    font-weight: normal;
    padding-left: 1rem;
  }
  </style>
