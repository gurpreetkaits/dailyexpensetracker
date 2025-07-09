<template>
  <div>
    <!-- Floating Feedback Button -->
    <button 
      @click="showModal = true"
      class="fixed bottom-20 sm:bottom-8 right-6 bg-blue-600 hover:bg-blue-700 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-200 z-50 group"
      title="Send Feedback"
    >
      <MessageCircle class="h-6 w-6" />
      <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-pulse">
        ?
      </span>
    </button>

    <!-- Feedback Modal -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div v-if="showModal"
           class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm"
           @click="showModal = false">
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="relative bg-white rounded-lg shadow-lg max-w-md w-full mx-4 p-6"
               @click.stop>
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-800">Send Feedback</h3>
              <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                <X class="h-5 w-5" />
              </button>
            </div>

            <!-- Feedback Form -->
            <form @submit.prevent="handleSubmitFeedback" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Your Feedback
                </label>
                <textarea 
                  v-model="feedback"
                  required 
                  placeholder="Let us know any feature requests or improvements!"
                  class="w-full rounded-lg bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 text-base text-gray-800 transition-all resize-none min-h-[120px]"
                  rows="4"
                ></textarea>
              </div>

              <div class="flex justify-end gap-3">
                <button 
                  type="button"
                  @click="showModal = false"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                >
                  Cancel
                </button>
                <button 
                  type="submit"
                  :disabled="isSaving || !feedback.trim()"
                  class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                  <span v-if="isSaving" class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                  {{ isSaving ? 'Sending...' : 'Send Feedback' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script>
import { MessageCircle, X } from 'lucide-vue-next'
import { storeFeedback } from '../services/SettingsService.js'
import { useNotifications } from '../composables/useNotifications'

export default {
  name: 'FloatingFeedbackButton',
  components: {
    MessageCircle,
    X
  },
  data() {
    return {
      showModal: false,
      feedback: '',
      isSaving: false
    }
  },
  methods: {
    async handleSubmitFeedback() {
      const { notify } = useNotifications()

      try {
        this.isSaving = true
        
        await storeFeedback({
          feedback: this.feedback
        })

        notify({
          title: 'Success',
          message: 'Feedback sent successfully! 🎉',
          type: 'success'
        })

        // Reset form and close modal
        this.feedback = ''
        this.showModal = false
      } catch (error) {
        console.error('Error sending feedback:', error)
        notify({
          title: 'Error',
          message: 'Failed to send feedback. Please try again.',
          type: 'error'
        })
      } finally {
        this.isSaving = false
      }
    }
  }
}
</script>

<style scoped>
/* Smooth transitions for the floating button */
.group:hover .absolute {
  transform: scale(1.1);
}

/* Ensure the button stays above other content */
.z-50 {
  z-index: 50;
}
</style> 