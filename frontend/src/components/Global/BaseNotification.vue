<template>
    <div
        :class="[
            'flex items-center p-2.5 mb-2 rounded-lg shadow-sm border backdrop-blur-md transition-all duration-500 transform max-w-sm',
            styles.bgColor,
            styles.borderColor,
            fadeOut ? 'opacity-0 translate-x-4 scale-95' : 'opacity-100 translate-x-0 scale-100'
        ]"
    >
        <!-- Icon with background -->
        <div :class="[
            'flex-shrink-0 w-6 h-6 rounded-md flex items-center justify-center text-xs font-medium',
            styles.iconBg,
            styles.iconColor
        ]">
            {{ styles.icon }}
        </div>

        <div class="ml-2.5 flex-1 min-w-0">
            <!-- Title and Message in one line if possible -->
            <div class="flex items-center gap-1.5">
                <slot name="title">
                    <h4 :class="['font-medium text-sm truncate', styles.textColor]">{{ title }}</h4>
                </slot>
                <slot name="message">
                    <p :class="['text-sm truncate', styles.textColor]">{{ message }}</p>
                </slot>
            </div>

            <!-- Progress bar -->
            <div class="mt-1.5 h-0.5 bg-gray-200/30 rounded-full overflow-hidden">
                <div 
                    :class="['h-full transition-all duration-100 rounded-full', styles.progressColor]"
                    :style="{ width: `${progress}%` }"
                ></div>
            </div>
        </div>

        <!-- Close button -->
        <button 
            @click="$emit('closed')"
            :class="[
                'ml-2 flex-shrink-0 p-0.5 rounded hover:bg-gray-100/50 transition-colors duration-200',
                'focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-offset-transparent',
                styles.textColor
            ]"
        >
            <span class="sr-only">Close</span>
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</template>

<script setup>
defineProps({
    avatar: { type: String, default: '' },
    title: { type: String, required: true },
    message: { type: String, default: '' },
    timeAgo: { type: String, default: '' },
    styles: { 
        type: Object, 
        required: true,
        default: () => ({
            icon: '✓',
            bgColor: 'bg-emerald-500/10',
            borderColor: 'border-emerald-500/20',
            textColor: 'text-emerald-700',
            iconColor: 'text-emerald-500',
            progressColor: 'bg-emerald-500',
            iconBg: 'bg-emerald-500/20'
        })
    },
    progress: { type: Number, default: 100 },
    fadeOut: { type: Boolean, default: false }
});

defineEmits(['closed']);
</script>

<style scoped>
.backdrop-blur-md {
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}
</style>
