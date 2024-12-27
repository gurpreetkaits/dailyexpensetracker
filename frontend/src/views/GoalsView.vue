<template>
  <div class="space-y-4 relative pb-24 m-3 lg:max-w-2xl lg:mx-auto">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-4">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <template v-else>
      <!-- Overview Card -->
      <div class="bg-white rounded-2xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ formatCurrency(balance, currencyCode) }}</h2>
            <p class="text-sm text-gray-500">Total Balance</p>
          </div>
          <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center">
            <Wallet class="h-6 w-6 text-blue-500" />
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-emerald-50 rounded-xl p-4">
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm text-emerald-600 font-medium">Total Saved</span>
              <PiggyBank class="h-4 w-4 text-emerald-500" />
            </div>
            <p class="text-lg font-semibold text-emerald-700">{{ formatCurrency(totalSaved, currencyCode) }}</p>
            <p class="text-xs text-emerald-600">{{ savingsProgress }}% of desired</p>
          </div>

          <div class="bg-blue-50 rounded-xl p-4">
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm text-blue-600 font-medium">Goals Target</span>
              <Target class="h-4 w-4 text-blue-500" />
            </div>
            <p class="text-lg font-semibold text-blue-700">{{ formatCurrency(totalDesired, currencyCode) }}</p>
            <p class="text-xs text-blue-600">Across {{ goals.length }} goals</p>
          </div>
        </div>

        <!-- Overall Progress Bar -->
        <div class="mt-4">
          <div class="relative pt-1">
            <div class="flex items-center justify-between mb-2">
              <div class="text-xs text-gray-600">Overall Progress</div>
              <div class="text-xs text-gray-600 font-semibold">{{ savingsProgress }}%</div>
            </div>
            <div class="overflow-hidden h-2 text-xs flex rounded-full bg-blue-100">
              <div :style="{ width: `${savingsProgress}%` }"
                class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500 transition-all duration-500">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Goals Section -->
      <div class="space-y-4">
        <!-- <div class="flex items-center justify-between px-1">
          <h3 class="text-lg font-semibold text-gray-800">Your Goals</h3>
          <button @click="openForm" 
                  class="text-blue-500 hover:text-blue-600 flex items-center gap-1">
            <Plus class="h-4 w-4" />
            <span class="text-sm">Add Goal</span>
          </button>
        </div> -->

        <!-- Goals List -->
        <div v-if="sortedGoals.length" class="space-y-4">
          <div v-for="goal in sortedGoals" :key="goal.id"
            class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer"
            @click="editGoal(goal)">
            <div class="flex items-start justify-between mb-3">
              <div>
                <h4 class="font-medium text-gray-900">{{ goal.name }}</h4>
                <p class="text-sm text-gray-500">Target: {{ formatCurrency(goal.target, currencyCode) }}</p>
              </div>
              <div class="bg-blue-50 px-3 py-1 rounded-full">
                <p class="text-sm font-medium text-blue-600">
                  {{ calculateProgress(goal) }}%
                </p>
              </div>
            </div>

            <div class="relative pt-1">
              <div class="overflow-hidden h-2 text-xs flex rounded-full bg-blue-100">
                <div :style="{ width: `${calculateProgress(goal)}%` }"
                  class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500 transition-all duration-500">
                </div>
              </div>
            </div>

            <div class="mt-3 flex justify-between items-center text-sm">
              <span class="text-gray-500">{{ formatDate(goal.target_date) }}</span>
              <span class="font-medium text-gray-900">
                {{ formatCurrency(goal.saved, currencyCode) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-xl p-8 text-center">
          <div class="bg-blue-50 h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4">
            <Target class="h-8 w-8 text-blue-500" />
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Set Your First Goal</h3>
          <p class="text-gray-500 text-sm mb-6">
            Start your journey by adding a financial goal to track your progress.
          </p>
          <button @click="openForm"
            class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition-colors">
            <Plus class="h-4 w-4 mr-2" />
            Add Goal
          </button>
        </div>
      </div>
    </template>

    <!-- Desktop Modal -->
    <TransitionRoot appear :show="showModal" as="template">
      <Dialog as="div" @close="closeModal" class="relative z-50 hidden md:block">
        <TransitionChild enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100"
          leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild enter="duration-300 ease-out" enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95">
              <DialogPanel
                class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-2 shadow-xl transition-all">
                <GoalForm :editing-goal="editingGoal" :loading="loading" @save="saveGoal" @delete="deleteGoal"
                  @cancel="closeModal" />
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Mobile Bottom Sheet -->
    <BottomSheet v-model="showBottomSheet" class="md:hidden">
      <div class="p-2 rounded-t-2xl bg-white">
        <GoalForm :editing-goal="editingGoal" :loading="loading" @save="saveGoal" @delete="deleteGoal"
          @cancel="closeModal" />
      </div>
    </BottomSheet>
    <button @click="openForm"
      class="fixed bottom-[80px] left-1/2 transform -translate-x-1/2 inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-500 text-white shadow-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
      <Plus class="h-6 w-6" />
    </button>
  </div>
</template>

<script>
import { useGoalStore } from '../store/goals'
import { Target, Plus, Wallet, PiggyBank, X } from 'lucide-vue-next'
import BottomSheet from '../components/BottomSheet.vue'
import GoalForm from '../components/GoalForm.vue'
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { mapState, mapActions } from 'pinia'
import { useSettingsStore } from '../store/settings'
import { numberMixin } from '../mixins/numberMixin'
import { useTransactionStore } from '../store/transaction'

export default {
  name: 'GoalsView',

  components: {
    Target,
    Plus,
    Wallet,
    PiggyBank,
    X,
    BottomSheet,
    GoalForm,
    Dialog,
    DialogPanel,
    TransitionRoot,
    TransitionChild
  },
  mixins: [numberMixin],
  data() {
    return {
      showModal: false,
      showBottomSheet: false,
      editingGoal: null,
    }
  },

  computed: {
    ...mapState(useGoalStore, [
      'goals',
      'loading',
      'mandatorySavings',
      'availableForGoals',
      'totalAllocatedToGoals',
      'remainingToAllocate',
      'sortedGoals'
    ]),
    ...mapState(useTransactionStore,['getBalance']),
    ...mapState(useSettingsStore, ['currencyCode']),
    balance(){
      return this.getBalance || 0
    },
    calculateProgress() {
      return (goal) => {
        const saved = parseFloat(goal.saved || 0)
        const target = parseFloat(goal.target || 1)
        return Math.min(Math.round((saved / target) * 100), 100)
      }
    },
    totalSaved() {
      return this.goals.reduce((sum, goal) => {
        return sum + parseFloat(goal.saved || 0)
      }, 0)
    },

    totalDesired() {
      return this.goals.reduce((sum, goal) => {
        return sum + parseFloat(goal.target || 0)
      }, 0)
    },

    savingsProgress() {
      if (this.totalDesired === 0) return 0
      return Math.min(Math.round((this.totalSaved / this.totalDesired) * 100), 100)
    },
  },

  methods: {
    ...mapActions(useGoalStore, [
      'fetchGoals',
      'fetchGoal',
      'addGoal',
      'updateGoal',
      'removeGoal',
      'updateGoalsSavings'
    ]),


    openForm() {
      this.editingGoal = null
      if (window.innerWidth >= 768) {
        this.showModal = true
      } else {
        this.showBottomSheet = true
      }
    },

    closeModal() {
      this.showModal = false
      this.showBottomSheet = false
      this.editingGoal = null
    },

    async editGoal(goal) {
      try {
        await this.fetchGoal(goal.id)
        this.editingGoal = goal
        if (window.innerWidth >= 768) {
          this.showModal = true
        } else {
          this.showBottomSheet = true
        }
      } catch (error) {
        console.error('Failed to fetch goal details:', error)
      }
    },

    async saveGoal(formData) {
      try {
        if (this.editingGoal) {
          await this.updateGoal({
            id: this.editingGoal.id,
            ...formData
          })
        } else {
          await this.addGoal(formData)
        }
        this.closeModal()
        await this.updateGoalsSavings()
      } catch (error) {
        console.error('Failed to save goal:', error)
      }
    },

    async deleteGoal() {
      if (confirm('Are you sure you want to delete this goal?')) {
        try {
          await this.removeGoal(this.editingGoal.id)
          this.closeModal()
        } catch (error) {
          console.error('Failed to delete goal:', error)
        }
      }
    },

  },

  async mounted() {
    try {
      await this.fetchGoals()
    } catch (error) {
      console.error('Failed to fetch goals:', error)
    }
  },

}
</script>

<style scoped>
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.hover\:shadow-md:hover {
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

/* Modal/Sheet Transitions */
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

.backdrop-enter-active,
.backdrop-leave-active {
  transition: opacity 0.3s ease;
}

.backdrop-enter-from,
.backdrop-leave-to {
  opacity: 0;
}

/* Progress Bar Animation */
.progress-bar-enter-active,
.progress-bar-leave-active {
  transition: width 0.5s ease;
}

/* Custom Scrollbar */
.goals-container {
  scrollbar-width: thin;
  scrollbar-color: rgba(0, 0, 0, 0.1) transparent;
}

.goals-container::-webkit-scrollbar {
  width: 6px;
}

.goals-container::-webkit-scrollbar-track {
  background: transparent;
}

.goals-container::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.1);
  border-radius: 3px;
}

/* Card Hover Effects */
.goal-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.goal-card:hover {
  transform: translateY(-2px);
}

/* Custom Focus Styles */
.focus-ring {
  @apply focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2;
}
</style>