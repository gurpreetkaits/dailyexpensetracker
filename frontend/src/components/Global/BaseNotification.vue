<template>
    <div
        class="flex items-start p-4 mb-2 bg-white rounded-lg shadow hover:shadow-md transition-shadow"
    >
        <!-- AVATAR SLOT (fallback to prop.avatar) -->
        <slot name="avatar">
            <img
                v-if="avatar"
                :src="avatar"
                alt="avatar"
                class="h-10 w-10 rounded-full object-cover flex-shrink-0"
            />
        </slot>

        <div class="ml-3 flex-1">
            <!-- HEADER: title + time -->
            <div class="flex justify-between items-center">
                <slot name="title">
                    <h4 class="font-semibold text-gray-800">{{ title }}</h4>
                </slot>
                <slot name="time">
                    <span class="text-xs text-gray-500">{{ timeAgo }}</span>
                </slot>
            </div>

            <!-- MESSAGE SLOT -->
            <slot name="message">
                <p class="text-sm text-gray-600 mt-1">{{ message }}</p>
            </slot>
        </div>
    </div>
</template>

<script setup>
defineProps({
    avatar:   { type: String, default: '' },
    title:    { type: String, required: true },
    message:  { type: String, default: '' },
    timeAgo:  { type: String, default: '' },
});
</script>
