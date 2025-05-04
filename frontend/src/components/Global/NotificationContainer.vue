<template>
    <!-- fixed top-right -->
    <div class="fixed top-4 right-4 space-y-2 z-50">
        <BaseNotification
            v-for="n in notifications"
            :key="n.id"
            :title="n.title"
            :message="n.message"
            :time-ago="n.timeAgo"
            :avatar="n.avatar"
            @closed="dismiss(n.id)"
        />
    </div>
</template>
<script setup>
import { useNotifications } from '../../composables/useNotifications'
import BaseNotification from './BaseNotification.vue'

const { list: notifications, dismiss } = useNotifications()
</script>
