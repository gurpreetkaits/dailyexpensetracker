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
            <textarea required placeholder="Let us know any feature requests or improvements!" class="w-full px-3 py-1 bg-transparent text-gray-800
                               border rounded-md focus:bg-white
                               text-sm focus:ring-0 transition-colors" v-model="feedback"></textarea>
        </div>
        <button type="submit"
                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center gap-2 disabled:opacity-50"
                :disabled="isSaving">
          <span v-if="isSaving"
                class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            {{ isSaving ? 'Sending...' : 'Send' }}
        </button>
    </form>

</template>

<style scoped>

</style>
