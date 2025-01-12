<template>
    <div v-if="stats" class="p-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm">Total Transactions</h3>
                <p class="text-2xl font-semibold">{{ formatNumber(stats[0].totalTransactions) }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm">Total Users</h3>
                <p class="text-2xl font-semibold">{{ formatNumber(stats[0].totalUsers) }}</p>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow py-8">
            <div class="divide-y" v-if="users">
                <div v-for="user in users" :key="user.id" class="ps-1 flex justify-between items-center">
                    <img :src="user.avatar" class="rounded-full object-cover object-cover rounded-full w-6 h-6" />
                    <div class="flex justify-start">
                        <p class="font-medium">{{ user.name }}</p>
                        <p class="text-sm text-gray-500">{{ user.email }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-between px-4 py-3 bg-white border-t">
                    <div class="flex justify-between flex-1 sm:hidden">
                        <button @click="fetchPage(pagination.current_page - 1)" :disabled="!pagination.prev_page_url"
                            class="btn-pagination"
                            :class="{ 'opacity-50 cursor-not-allowed': !pagination.prev_page_url }">
                            Previous
                        </button>
                        <button @click="fetchPage(pagination.current_page + 1)" :disabled="!pagination.next_page_url"
                            class="btn-pagination"
                            :class="{ 'opacity-50 cursor-not-allowed': !pagination.next_page_url }">
                            Next
                        </button>
                    </div>

                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ pagination.from }}</span>
                                to
                                <span class="font-medium">{{ pagination.to }}</span>
                                of
                                <span class="font-medium">{{ pagination.total }}</span>
                                results
                            </p>
                        </div>

                        <div>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                                <button v-for="link in pagination.links" :key="link.label"
                                    @click="fetchPage(link.label)" :disabled="!link.url"
                                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold" :class="[
                                        link.active ? 'bg-blue-50 text-blue-600' : 'text-gray-900',
                                        !link.url ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'
                                    ]" v-html="link.label">
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
                <div v-if="!users.length" class="p-4 text-center text-gray-500">
                    No transactions found
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import dayjs from 'dayjs'

export default {
    name: 'DashboardView',
    props: {
        stats: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            users: [],
            pagination: [],
        }
    },
    methods: {
        formatNumber(number) {
            return new Intl.NumberFormat('en-IN').format(number)
        },
        formatDate(date) {
            return dayjs(date).format('MMM D, YYYY')
        },
        fetchPage(page) {
            this.$emit('loadStats', page)
        }
    },
    created() {
        this.users = this.stats[1]?.data
        this.pagination = this.stats[1]
    }
}
</script>
