import { reactive, readonly } from 'vue'

const state = reactive({
    list: []
})
let seq = 0

export function useNotifications() {
    function notify({ title, message, type = 'success', duration = 3000 }) {
        const id = seq++
        state.list.push({ id, title, message, type })
        setTimeout(() => dismiss(id), duration)
    }

    function dismiss(id) {
        const i = state.list.findIndex(n => n.id === id)
        if (i !== -1) state.list.splice(i, 1)
    }

    return {
        list: readonly(state.list),
        notify,
        dismiss
    }
}
