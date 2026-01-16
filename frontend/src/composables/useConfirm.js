import { reactive, readonly } from 'vue'

const state = reactive({
    show: false,
    title: '',
    message: '',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    type: 'danger', // 'danger' | 'warning' | 'info'
    resolve: null
})

export function useConfirm() {
    function confirm({
        title = 'Are you sure?',
        message = '',
        confirmText = 'Confirm',
        cancelText = 'Cancel',
        type = 'danger'
    } = {}) {
        return new Promise((resolve) => {
            state.title = title
            state.message = message
            state.confirmText = confirmText
            state.cancelText = cancelText
            state.type = type
            state.resolve = resolve
            state.show = true
        })
    }

    function handleConfirm() {
        state.show = false
        if (state.resolve) {
            state.resolve(true)
            state.resolve = null
        }
    }

    function handleCancel() {
        state.show = false
        if (state.resolve) {
            state.resolve(false)
            state.resolve = null
        }
    }

    return {
        state: readonly(state),
        confirm,
        handleConfirm,
        handleCancel
    }
}
