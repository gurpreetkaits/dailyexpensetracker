<template>
  <div class="max-w-4xl mx-auto">
    <ExpenseList :expenses="expenses" :categories="allCategories" @delete="deleteExpense"
      @add-expense="showExpenseForm = true" />
  </div>

  <ExpenseForm :is-visible="showExpenseForm" :categories="allCategories" @close="showExpenseForm = false"
    @save="addExpense" />

  <!-- Survey Modal -->
  <div v-if="showSurvey" class="fixed inset-0 bg-gray-800 bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-auto shadow-xl transform transition-all">
      <!-- Step 1: What are you looking for? -->
      <div v-if="surveyStep === 1">
        <h3 class="text-xl font-semibold mb-2 text-gray-800">Welcome to Daily Expense Tracker!</h3>
        <p class="text-sm text-gray-600 mb-4">To help us improve, what are you primarily looking to achieve?</p>
        <div class="space-y-2 mb-4">
          <label v-for="option in userNeedsOptions" :key="option" class="flex items-center p-3 border rounded-lg hover:bg-gray-100 cursor-pointer transition-colors">
            <input type="checkbox" :value="option" v-model="selectedNeeds" class="form-checkbox h-5 w-5 text-emerald-600 rounded focus:ring-emerald-500">
            <span class="ml-3 text-gray-700">{{ option }}</span>
          </label>
          <input type="text" v-model="otherNeed" placeholder="Something else? Let us know..." class="w-full p-2 border rounded-lg mt-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
        </div>
        <button @click="nextSurveyStep" class="w-full bg-emerald-600 text-white p-3 rounded-lg hover:bg-emerald-700 transition-colors font-medium">
          Next
        </button>
      </div>
      <!-- Step 2: Happiness and Willingness to Pay -->
      <div v-if="surveyStep === 2">
        <h3 class="text-xl font-semibold mb-4 text-gray-800">Any</h3>
        <div class="mb-4">
          <p class="text-gray-700 mb-2">
            How much would you be comfortable paying if we serve you better? 
          </p>
          <input type="text" v-model="comfortablePay" placeholder="e.g. $5/month, $20/year, etc." class="w-full p-2 border rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500" />
        </div>
        <!-- <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-1" for="feedbackText">Any other comments? (Optional)</label>
          <textarea v-model="feedbackText" id="feedbackText" rows="3" class="w-full p-2 border rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500"></textarea>
        </div> -->
        <button @click="submitSurvey" class="w-full bg-emerald-600 text-white p-3 rounded-lg hover:bg-emerald-700 transition-colors font-medium">
          Submit & Continue
        </button>
        <div v-if="errorMessage" class="mt-4 p-3 bg-red-100 text-red-700 rounded">
          {{ errorMessage }}
        </div>
      </div>
    </div>
  </div>
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
