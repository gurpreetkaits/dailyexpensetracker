<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="state.show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="handleCancel"></div>

                <!-- Dialog -->
                <Transition name="scale">
                    <div v-if="state.show" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                        <!-- Icon -->
                        <div class="flex justify-center pt-6">
                            <div
                                class="w-14 h-14 rounded-full flex items-center justify-center"
                                :class="iconBgClass"
                            >
                                <component :is="iconComponent" class="w-7 h-7" :class="iconClass" />
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="px-6 pt-4 pb-6 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ state.title }}</h3>
                            <p v-if="state.message" class="text-sm text-gray-500">{{ state.message }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex border-t border-gray-100">
                            <button
                                @click="handleCancel"
                                class="flex-1 py-3.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors"
                            >
                                {{ state.cancelText }}
                            </button>
                            <button
                                @click="handleConfirm"
                                class="flex-1 py-3.5 text-sm font-medium border-l border-gray-100 transition-colors"
                                :class="confirmBtnClass"
                            >
                                {{ state.confirmText }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed } from 'vue'
import { AlertTriangle, AlertCircle, Info, Trash2 } from 'lucide-vue-next'
import { useConfirm } from '../../composables/useConfirm'

const { state, handleConfirm, handleCancel } = useConfirm()

const iconComponent = computed(() => {
    switch (state.type) {
        case 'danger': return Trash2
        case 'warning': return AlertTriangle
        case 'info': return Info
        default: return AlertCircle
    }
})

const iconBgClass = computed(() => {
    switch (state.type) {
        case 'danger': return 'bg-red-100'
        case 'warning': return 'bg-amber-100'
        case 'info': return 'bg-blue-100'
        default: return 'bg-gray-100'
    }
})

const iconClass = computed(() => {
    switch (state.type) {
        case 'danger': return 'text-red-600'
        case 'warning': return 'text-amber-600'
        case 'info': return 'text-blue-600'
        default: return 'text-gray-600'
    }
})

const confirmBtnClass = computed(() => {
    switch (state.type) {
        case 'danger': return 'text-red-600 hover:bg-red-50'
        case 'warning': return 'text-amber-600 hover:bg-amber-50'
        case 'info': return 'text-blue-600 hover:bg-blue-50'
        default: return 'text-gray-900 hover:bg-gray-50'
    }
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.scale-enter-active,
.scale-leave-active {
    transition: all 0.2s ease;
}

.scale-enter-from,
.scale-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>
