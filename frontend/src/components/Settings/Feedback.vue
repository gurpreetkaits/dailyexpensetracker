<script>
import {storeFeedback} from "../../services/SettingsService.js";
import { useNotifications } from '../../composables/useNotifications'

export default {
    name: "Feedback",
    data(){
        return {
            feedback: '',
            isSaving: false,
            alertMessage: "",
            alertStatus: "success", // "success" or "error"
        }
    },
    methods:{
        async handleSaveSettings() {
            const { notify } = useNotifications()

            try {
                this.isSaving = true
                let params = {
                    feedback: this.feedback,
                }
              await storeFeedback(params);

                notify({
                    title: 'Success',
                    message: 'Feedback sent 🎉',
                    type: 'success'
                })
            } catch (e) {
                console.log(e)
                notify({
                    title: 'Error',
                    message: 'Failed to send feedback',
                    type: 'error'
                })
            } finally {
                this.isSaving = false
                this.feedback = '';
                setTimeout(() => (this.alertMessage = ""), 4000);
            }
        },
    }
}
</script>

<template>
    <form @submit.prevent="handleSaveSettings">
        <div>
            <textarea required placeholder="Let us know any feature requests or improvements!"
                class="w-full rounded-lg bg-gray-50 border-none focus:ring-2 focus:ring-blue-500 p-3 text-base text-gray-800 transition-all resize-none min-h-[90px]"
                v-model="feedback"></textarea>
        </div>
        <button type="submit"
                :disabled="isSaving"
                class="w-full py-2.5 rounded-lg text-sm font-medium transition-all flex items-center justify-center gap-2"
                :class="isSaving ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-blue-50 text-blue-600 hover:bg-blue-100'">
          <span v-if="isSaving"
                class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            {{ isSaving ? 'Sending...' : 'Send' }}
        </button>
    </form>

</template>

<style scoped>

</style>
