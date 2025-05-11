<template>
  <div class="py-1">
    <div class="lg:max-w-2xl lg:mx-auto">
      <ExpenseList :expenses="expenses" :categories="allCategories" @delete="deleteExpense"
        @add-expense="showExpenseForm = true" />
    </div>
  </div>

  <ExpenseForm :is-visible="showExpenseForm" :categories="allCategories" @close="showExpenseForm = false"
    @save="addExpense" />
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useLocalStorage } from '@vueuse/core'
import ExpenseForm from '../components/ExpenseForm.vue'
import ExpenseList from '../components/ExpenseList.vue'
import { getCategories } from '../services/SettingsService'

const showExpenseForm = ref(false)
const globalCategories = ref([])
const userCategories = ref([])

const allCategories = computed(() => {
  return [...globalCategories.value, ...userCategories.value]
})

const expenses = useLocalStorage('expenses', [])

const fetchCategories = async () => {
  try {
    const response = await getCategories()

    globalCategories.value = response.filter(cat => !cat.is_custom)
    userCategories.value = response.filter(cat => cat.is_custom)
  } catch (error) {
    console.error('Error fetching categories:', error)
  }
}

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

onMounted(async () => {
  await fetchCategories()
})
</script>