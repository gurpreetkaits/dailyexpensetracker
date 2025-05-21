<template>
  <div class="mx-auto">
    <ExpenseList :expenses="expenses" :categories="allCategories" @delete="deleteExpense"
      @add-expense="showExpenseForm = true" />
  </div>

  <ExpenseForm :is-visible="showExpenseForm" :categories="allCategories" @close="showExpenseForm = false"
    @save="addExpense" />
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useLocalStorage } from '@vueuse/core'
import ExpenseForm from '../components/ExpenseForm.vue'
import ExpenseList from '../components/ExpenseList.vue'
import { getCategories } from '../services/SettingsService'
import { useAuthStore } from '../store/auth'
import { submitSurvey as submitSurveyAPI } from '../services/AuthService'
import { iconMixin } from '../mixins/iconMixin'

export default {
  components: { ExpenseForm, ExpenseList },
  mixins: [iconMixin],
  setup() {
    // Expense logic
    const showExpenseForm = ref(false)
    const globalCategories = ref([])
    const userCategories = ref([])
    const allCategories = computed(() => [...globalCategories.value, ...userCategories.value])
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
      expenses.value.push({ ...expense, id: Date.now() })
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
    // Survey logic
    const authStore = useAuthStore()
    const showSurvey = ref(false)
    const surveyStep = ref(1)
    const userNeedsOptions = [
      'Detailed expense tracking',
      'Easy budgeting tools',
      'AI-powered financial insights (Chat)',
      'Customizable financial reports',
      'Tracking multiple bank accounts/wallets'
    ]
    const selectedNeeds = ref([])
    const otherNeed = ref('')
    const isHappy = ref(null)
    const comfortablePay = ref('')
    const feedbackText = ref('')
    const errorMessage = ref('')
    const formattedNeedsQuery = computed(() => {
      const allNeeds = [...selectedNeeds.value]
      if (otherNeed.value.trim()) {
        allNeeds.push(otherNeed.value.trim())
      }
      if (allNeeds.length === 0) return 'your primary financial goals'
      if (allNeeds.length === 1) return allNeeds[0]
      if (allNeeds.length === 2) return allNeeds.join(' and ')
      return allNeeds.slice(0, -1).join(', ') + ', and ' + allNeeds.slice(-1)
    })
    const showPostLoginSurvey = () => {
      showSurvey.value = JSON.parse(authStore.user?.settings)?.survey ? false : true
      surveyStep.value = 1
      selectedNeeds.value = []
      otherNeed.value = ''
      isHappy.value = null
      comfortablePay.value = ''
      feedbackText.value = ''
    }
    const nextSurveyStep = () => {
      if (surveyStep.value === 1) {
        if (selectedNeeds.value.length === 0 && !otherNeed.value.trim()) {
          errorMessage.value = 'Please select at least one option or tell us what you\'re looking for.'
          setTimeout(() => (errorMessage.value = ''), 3000)
          return
        }
        errorMessage.value = ''
        surveyStep.value = 2
      }
    }
    const submitSurvey = async () => {
      if (!comfortablePay.value.trim()) {
        errorMessage.value = 'Please enter how much you would be comfortable paying.'
        setTimeout(() => (errorMessage.value = ''), 3000)
        return
      }
      errorMessage.value = ''
      const surveyData = {
        userId: authStore.user?.id,
        userEmail: authStore.user?.email,
        selectedNeeds: selectedNeeds.value,
        otherNeed: otherNeed.value.trim(),
        comfortablePay: comfortablePay.value,
        feedbackText: feedbackText.value.trim(),
        timestamp: new Date().toISOString()
      }
      if (window.posthog) {
        window.posthog.capture('post_login_survey_completed', surveyData)
      }
      try {
        await submitSurveyAPI(surveyData)
        // Refresh user data from backend to check if survey is saved
        await authStore.getAuth()
        let settings = authStore.user && authStore.user.settings
        if (typeof settings === 'string') {
          try {
            settings = JSON.parse(settings)
          } catch {
            settings = {}
          }
        }
        if (settings && settings.survey) {
          showSurvey.value = false
        } else {
          errorMessage.value = 'Survey not saved. Please try again.'
        }
      } catch (e) {
        errorMessage.value = 'Failed to save survey. Please try again.'
      }
    }
    // Show survey only if not completed (check backend)
    onMounted(async () => {
      await fetchCategories()
      await authStore.getAuth()
      if (authStore.user && (!authStore.user.settings || !authStore.user.settings.survey)) {
        showPostLoginSurvey()
      }
    })
    return {
      showExpenseForm,
      globalCategories,
      userCategories,
      allCategories,
      expenses,
      fetchCategories,
      addExpense,
      deleteExpense,
      // Survey
      showSurvey,
      surveyStep,
      userNeedsOptions,
      selectedNeeds,
      otherNeed,
      isHappy,
      comfortablePay,
      feedbackText,
      errorMessage,
      formattedNeedsQuery,
      showPostLoginSurvey,
      nextSurveyStep,
      submitSurvey
    }
  }
}
</script>
