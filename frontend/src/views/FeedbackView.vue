<template>
  <div class="max-w-lg mx-auto px-4 py-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
      <div class="mb-6">
        <h1 class="text-xl font-semibold text-gray-900">Send Feedback</h1>
        <p class="text-sm text-gray-500 mt-1">Help us improve by sharing your thoughts</p>
      </div>

      <form @submit.prevent="handleSubmitFeedback" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Your Feedback
          </label>
          <textarea
            v-model="feedback"
            required
            placeholder="Let us know any feature requests or improvements!"
            class="w-full rounded-lg bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 text-base text-gray-800 transition-all resize-none min-h-[150px]"
            rows="5"
          ></textarea>
        </div>

        <button
          type="submit"
          :disabled="isSaving || !feedback.trim()"
          class="w-full py-3 rounded-lg text-sm font-medium transition-all flex items-center justify-center gap-2"
          :class="isSaving || !feedback.trim()
            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
            : 'bg-blue-600 text-white hover:bg-blue-700'"
        >
          <span v-if="isSaving" class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
          {{ isSaving ? 'Sending...' : 'Send Feedback' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script>
import { storeFeedback } from '../services/SettingsService.js'
import { useNotifications } from '../composables/useNotifications'

export default {
  name: 'FeedbackView',
  setup() {
    const { notify } = useNotifications()
    return { notify }
  },
  data() {
    return {
      feedback: '',
      isSaving: false
    }
  },
  methods: {
    async handleSubmitFeedback() {
      try {
        this.isSaving = true

        await storeFeedback({
          feedback: this.feedback
        })

        this.notify({
          title: 'Success',
          message: 'Feedback sent successfully!',
          type: 'success'
        })

        this.feedback = ''

        // Navigate back after success
        setTimeout(() => {
          this.$router.back()
        }, 1500)
      } catch (error) {
        console.error('Error sending feedback:', error)
        this.notify({
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
