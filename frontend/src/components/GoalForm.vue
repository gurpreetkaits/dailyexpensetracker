<template>
    <div class="space-y-6 py-3 px-3">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900">
                {{ editingGoal ? 'Edit Goal' : 'New Goal' }}
            </h2>
            <button @click="$emit('cancel')" class="text-gray-400 hover:text-gray-500">
                <X class="h-5 w-5" />
            </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-3 lg:gap-2 lg:grid lg:grid-cols-2 px-2 py-3">
            <!-- Target Date -->
            <div>
                <label class="block text-xs font-small text-gray-700 mb-1">
                    Target Date
                </label>
                <div class="border border-gray-100 rounded-md hover:bg-gray-50">
                    <input 
                        v-model="form.target_date" 
                        type="date"
                        class="w-full px-3 py-1 bg-transparent text-gray-800
                               border border-transparent rounded-md focus:bg-white
                               text-sm focus:ring-0 transition-colors"
                        :min="tomorrow" 
                        required 
                    />
                </div>
            </div>

            <!-- Goal Name -->
            <div style="margin-top: 0;">
                <label class="block text-xs font-small text-gray-700 mb-1 lg:mb-0">
                    Goal Name
                </label>
                <div class="border border-gray-100 rounded-md lg:mt-1 hover:bg-gray-50">
                    <input 
                        v-model="form.name" 
                        type="text"
                        class="w-full px-3 py-1 bg-transparent text-gray-800 placeholder-gray-400
                               border border-transparent rounded-md focus:bg-white
                               text-sm focus:ring-0 transition-colors"
                        placeholder="e.g., New MacBook" 
                        required 
                    />
                </div>
            </div>

            <!-- Target Amount -->
            <div>
                <label class="block text-xs font-small text-gray-700 mb-1">
                    Target Amount
                </label>
                <div class="border border-gray-100 rounded-md hover:bg-gray-50">
                    <div class="flex items-center gap-0">
                        <span class="text-sm px-2 text-gray-400">{{ currencySymbol }}</span>
                        <div class="relative flex-1">
                            <input 
                                v-model.number="form.target" 
                                type="number"
                                class="w-full py-1 px-2 bg-transparent text-gray-800 placeholder-gray-400
                                       border border-transparent rounded-md focus:bg-white
                                       text-sm focus:ring-0 transition-colors"
                                placeholder="0.00" 
                                required 
                                min="0" 
                                step="0.01" 
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amount Saved -->
            <div>
                <label class="block text-xs font-small text-gray-700 mb-1">
                    Amount Saved
                </label>
                <div class="border border-gray-100 rounded-md hover:bg-gray-50">
                    <div class="flex items-center gap-0">
                        <span class="text-sm px-2 text-gray-400">{{ currencySymbol }}</span>
                        <div class="relative flex-1">
                            <input 
                                v-model.number="form.saved" 
                                type="number"
                                class="w-full py-1 px-2 bg-transparent text-gray-800 placeholder-gray-400
                                       border border-transparent rounded-md focus:bg-white
                                       text-sm focus:ring-0 transition-colors"
                                placeholder="0.00" 
                                required 
                                min="0" 
                                step="0.01"
                                :max="form.target" 
                            />
                        </div>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1 lg:hidden">
                    Enter how much you've saved towards this goal
                </p>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                :disabled="loading || form.saved > form.target"
                class="w-full bg-blue-100 text-blue-600 py-2 px-3 rounded-md hover:bg-gray-200 
                       transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span v-if="!loading">{{ editingGoal ? 'Update Goal' : 'Create Goal' }}</span>
                <span v-else class="flex items-center justify-center">
                    <svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                        <path class="opacity-75" fill="currentColor" 
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    Processing...
                </span>
            </button>

            <!-- Delete Button -->
            <button 
                v-if="editingGoal" 
                type="button" 
                :disabled="loading" 
                @click="$emit('delete')"
                class="w-full border  py-2 px-3 rounded-md 
                       bg-red-50 text-red-500 border-red-100
                       transition-colors text-sm disabled:opacity-50"
            >
                Delete Goal
            </button>
        </form>
    </div>
</template>

<script>
// The script section remains exactly the same as your original
import { X } from 'lucide-vue-next'
import { ref, computed, watch } from 'vue'
import { useSettingsStore } from '../store/settings'
import { storeToRefs } from 'pinia'

export default {
    name: 'GoalForm',
    components: { X },
    props: {
        editingGoal: {
            type: Object,
            default: null
        },
        loading: {
            type: Boolean,
            default: false
        }
    },
    emits: ['save', 'delete', 'cancel'],
    setup(props, { emit }) {
        const settingsStore = useSettingsStore()
        const { currencySymbol } = storeToRefs(settingsStore)

        const form = ref({
            name: '',
            target: null,
            target_date: null,
            saved: 0
        })

        const tomorrow = computed(() => {
            const date = new Date()
            date.setDate(date.getDate() + 1)
            return date.toISOString().split('T')[0]
        })

        watch(() => props.editingGoal, (newGoal) => {
            if (newGoal) {
                form.value = {
                    name: newGoal.name,
                    target: parseFloat(newGoal.target),
                    target_date: newGoal.target_date,
                    saved: parseFloat(newGoal.saved) || 0
                }
            } else {
                form.value = {
                    name: '',
                    target: null,
                    target_date: null,
                    saved: 0
                }
            }
        }, { immediate: true })

        const handleSubmit = () => {
            if (form.value.saved > form.value.target) {
                alert('Saved amount cannot be greater than target amount')
                return
            }
            emit('save', { ...form.value })
        }

        return {
            form,
            tomorrow,
            handleSubmit,
            currencySymbol
        }
    }
}
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