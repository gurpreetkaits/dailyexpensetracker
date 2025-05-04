<template>
    <div class="p-6 ml-28">
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <h2 class="text-lg font-semibold px-6 py-4 border-b">Feedback</h2>

            <!-- table -->
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-3">#</th>
                    <th class="px-6 py-3">User</th>
                    <th class="px-6 py-3">Message</th>
                    <th class="px-6 py-3">Submitted</th>
                </tr>
                </thead>
                <tbody class="divide-y">
                <tr v-for="f in feedbacks" :key="f.id">
                    <td class="px-6 py-3 font-mono text-gray-500">{{ f.id }}</td>
                    <td class="px-6 py-3">{{ f.user }}</td>
                    <td class="px-6 py-3 max-w-xl truncate" :title="f.feedback">{{ f.feedback }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ ago(f.date) }}</td>
                </tr>

                <tr v-if="!feedbacks.length">
                    <td colspan="4" class="px-6 py-6 text-center text-gray-500">
                        No feedback yet.
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- pagination -->
            <div class="flex justify-between items-center px-6 py-4 border-t">
                <button
                    class="px-3 py-1 rounded bg-gray-100 hover:bg-gray-200 disabled:opacity-50"
                    :disabled="!pagination.prev_page_url"
                    @click="load(pagination.current_page - 1)">
                    Previous
                </button>

                <p class="text-sm text-gray-600">
                    Page {{ pagination.current_page }} of {{ pagination.last_page }}
                </p>

                <button
                    class="px-3 py-1 rounded bg-gray-100 hover:bg-gray-200 disabled:opacity-50"
                    :disabled="!pagination.next_page_url"
                    @click="load(pagination.current_page + 1)">
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import {getFeedbacks} from "../../services/FeedbackService.js";

export default {
    name: 'FeedbackList',

    data () {
        return {
            feedbacks : [],
            pagination: {
                current_page : 1,
                last_page    : 1,
                next_page_url: null,
                prev_page_url: null,
            }
        }
    },

    methods: {
        async load(page = 1) {
            const r = await getFeedbacks()
            const p = await r.data
            this.feedbacks  = p.data.map(f => ({
                id      : f.id,
                user    : f.user.name ?? '—',
                feedback : f.feedback,
                date    : f.created_at,
            }))
            this.pagination = p
        },

        ago(iso) {
            const diff = (Date.now() - Date.parse(iso)) / 1000
            const m  = Math.floor(diff / 60)
            if (m < 60) return `${m}month ago`
            const h = Math.floor(m / 60)
            if (h < 24) return `${h}hour ago`
            return `${Math.floor(h / 24)}day ago`
        },
    },

    created() {
        this.load(1)
    }
}
</script>

<style scoped>
/* keep avatars crisp if you ever add them later */
img { image-rendering: -webkit-optimize-contrast; }
</style>
