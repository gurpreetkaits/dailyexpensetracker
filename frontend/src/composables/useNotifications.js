import { reactive, readonly } from 'vue'

const state = reactive({
    list: []
})
let seq = 0

export function useNotifications() {
    function notify({ title, message, type = 'success', duration = 4000 }) {
        const id = seq++

        state.list.push({
            id,
            title,
            message,
            type,
            fadeOut: false
        })

        setTimeout(() => dismiss(id), duration)
    }

    function dismiss(id) {
        const i = state.list.findIndex(n => n.id === id)
        if (i !== -1) {
            // Add fade-out animation
            state.list[i].fadeOut = true
            setTimeout(() => {
                const index = state.list.findIndex(n => n.id === id)
                if (index !== -1) state.list.splice(index, 1)
            }, 300)
        }
    }

    // Helper to show validation errors from Laravel
    function showValidationErrors(errorResponse) {
        if (errorResponse?.response?.data?.errors) {
            const errors = errorResponse.response.data.errors
            // Show the first error message from each field
            Object.values(errors).forEach(fieldErrors => {
                if (Array.isArray(fieldErrors) && fieldErrors.length > 0) {
                    notify({ message: fieldErrors[0], type: 'error' })
                }
            })
        } else if (errorResponse?.response?.data?.message) {
            notify({ message: errorResponse.response.data.message, type: 'error' })
        } else if (errorResponse?.message) {
            notify({ message: errorResponse.message, type: 'error' })
        } else {
            notify({ message: 'An unexpected error occurred', type: 'error' })
        }
    }

    // Shorthand methods
    const success = (message, duration) => notify({ message, type: 'success', duration })
    const error = (message, duration) => notify({ message, type: 'error', duration })
    const warning = (message, duration) => notify({ message, type: 'warning', duration })
    const info = (message, duration) => notify({ message, type: 'info', duration })

    return {
        list: readonly(state.list),
        notify,
        dismiss,
        showValidationErrors,
        success,
        error,
        warning,
        info
    }
}
