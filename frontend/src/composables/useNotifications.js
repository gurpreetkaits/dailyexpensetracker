import { reactive, readonly } from 'vue'

const state = reactive({
    list: []
})
let seq = 0

const notificationTypes = {
    success: {
        icon: '✓',
        bgColor: 'bg-emerald-200 backdrop-blur-md',
        borderColor: 'border-emerald-200/20',
        textColor: 'text-emerald-700',
        iconColor: 'text-emerald-500',
        progressColor: 'bg-emerald-500',
        iconBg: 'bg-emerald-500/20'
    },
    error: {
        icon: '!',
        bgColor: 'bg-red-200 backdrop-blur-md',
        borderColor: 'border-red-200/20',
        textColor: 'text-red-700',
        iconColor: 'text-red-500',
        progressColor: 'bg-red-500',
        iconBg: 'bg-red-500/20'
    },
    warning: {
        icon: '⚠',
        bgColor: 'bg-amber-200 backdrop-blur-md',
        borderColor: 'border-amber-200/20',
        textColor: 'text-amber-700',
        iconColor: 'text-amber-500',
        progressColor: 'bg-amber-500',
        iconBg: 'bg-amber-500/20'
    },
    info: {
        icon: 'ℹ',
        bgColor: 'bg-blue-200 backdrop-blur-md',
        borderColor: 'border-blue-200/20',
        textColor: 'text-blue-700',
        iconColor: 'text-blue-500',
        progressColor: 'bg-blue-500',
        iconBg: 'bg-blue-500/20'
    }
}

export function useNotifications() {
    function notify({ title, message, type = 'success', duration = 5000 }) {
        const id = seq++
        const notificationType = notificationTypes[type] || notificationTypes.info
        
        state.list.push({ 
            id, 
            title, 
            message, 
            type,
            styles: notificationType,
            progress: 100
        })

        // Start progress animation
        const startTime = Date.now()
        const animate = () => {
            const elapsed = Date.now() - startTime
            const progress = Math.max(0, 100 - (elapsed / duration) * 100)
            
            const notification = state.list.find(n => n.id === id)
            if (notification) {
                notification.progress = progress
            }

            if (progress > 0) {
                requestAnimationFrame(animate)
            }
        }
        requestAnimationFrame(animate)

        setTimeout(() => dismiss(id), duration)
    }

    function dismiss(id) {
        const i = state.list.findIndex(n => n.id === id)
        if (i !== -1) {
            // Add fade-out animation
            const notification = state.list[i]
            notification.fadeOut = true
            setTimeout(() => {
                const index = state.list.findIndex(n => n.id === id)
                if (index !== -1) state.list.splice(index, 1)
            }, 500) // Increased fade-out duration
        }
    }

    return {
        list: readonly(state.list),
        notify,
        dismiss,
        types: Object.keys(notificationTypes)
    }
}
