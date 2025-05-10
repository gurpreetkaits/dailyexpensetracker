<template>
  <div class="px-2 sm:px-6 md:px-10 lg:px-16 ml-0 sm:ml-28">
    <div class="w-full">
      <ExpenseList :expenses="expenses" :categories="categories" @delete="deleteExpense"
        @add-expense="showExpenseForm = true" />
    </div>
  </div>

  <ExpenseForm :is-visible="showExpenseForm" :categories="categories" @close="showExpenseForm = false"
    @save="addExpense" />
</template>

<script setup>
import { ref } from 'vue'
import { useLocalStorage } from '@vueuse/core'
import ExpenseForm from '../components/ExpenseForm.vue'
import ExpenseList from '../components/ExpenseList.vue'

const showExpenseForm = ref(false)

const categories = [
  { id: 'subscriptions', name: 'Subscriptions' },
  { id: 'utilities', name: 'Utilities' },
  { id: 'rent', name: 'Rent/Mortgage' },
  { id: 'transport', name: 'Transportation' },
  { id: 'other', name: 'Other' },
  { id: 'emi', name: 'EMI' }
]

const expenses = useLocalStorage('expenses', [])

const addExpense = (expense) => {
  expenses.value.push({
    ...expense,
    id: Date.now()
  })
  showExpenseForm.value = false
}

const deleteExpense = (id) => {
  if (confirm('Are you sure you want to delete this expense?')) {
    expenses.value = expenses.value.filter(expense => expense.id !== id)
  }
}
</script>
