<template>
    <div v-if="isVisible" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg w-full max-w-sm shadow-xl">
        <!-- Header -->
        <div class="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-lg">
          <h2 class="text-base font-semibold text-gray-700">Add New Expense</h2>
          <button 
            @click="$emit('close')" 
            class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100 transition-colors"
          >
            <X class="h-4 w-4" />
          </button>
        </div>
  
        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="p-4 space-y-4">
          <!-- Name Input -->
          <div class="space-y-1">
            <label class="text-xs font-medium text-gray-600">Expense Name</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full p-2.5 text-sm border rounded-md bg-gray-50 focus:bg-white 
                     focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              placeholder="e.g., Netflix, Rent, Phone Bill"
              required
            />
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
                v-for="category in categories" 
                :key="category.id" 
                :value="category.id"
                class="py-1"
              >
                {{ category.name }}
              </option>
            </select>
          </div>
  
          <!-- Date Input -->
          <div class="space-y-1">
            <label class="text-xs font-medium text-gray-600">Start Date</label>
            <input
              v-model="form.startDate"
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
              class="flex-1 py-2.5 px-4 text-sm bg-blue-500 text-white rounded-md 
                     hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 
                     focus:ring-offset-2 transition-colors"
            >
              Save Expense
            </button>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script setup>
  import { reactive } from 'vue'
  import { X, DollarSign } from 'lucide-vue-next'
  
  const props = defineProps({
    isVisible: Boolean,
    categories: {
      type: Array,
      default: () => [
        { id: 'subscriptions', name: 'Subscriptions' },
        { id: 'utilities', name: 'Utilities' },
        { id: 'rent', name: 'Rent/Mortgage' },
        { id: 'transport', name: 'Transportation' },
        { id: 'emi', name: 'EMI' },
        { id: 'other', name: 'Other' }
      ]
    }
  })
  
  const emit = defineEmits(['close', 'save'])
  
  const form = reactive({
    name: '',
    amount: '',
    category: '',
    recurrence: 'monthly',
    startDate: ''
  })
  
  const handleSubmit = () => {
    emit('save', { ...form, amount: parseFloat(form.amount) })
    Object.assign(form, {
      name: '',
      amount: '',
      category: '',
      recurrence: 'monthly',
      startDate: ''
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
  </style>