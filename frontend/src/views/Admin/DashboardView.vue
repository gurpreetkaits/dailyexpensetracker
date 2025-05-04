<!-- DashboardView.vue -->
<template>
    <div class="p-6 ml-28">
        <!-- Metric cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm">TotalUsers</h3>
                <p class="text-2xl font-semibold">{{ fmtNum(stats.totalUsers) }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm">ActiveUsers (15min)</h3>
                <p class="text-2xl font-semibold">{{ fmtNum(stats.activeUsers) }}</p>
            </div>
        </div>

        <!-- Recent users table -->
        <div class="bg-white rounded-lg shadow mb-10 overflow-x-auto">
            <h2 class="text-lg font-semibold px-6 py-4 border-b">RecentUsers</h2>

            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-3">#</th>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Joined</th>
                </tr>
                </thead>
                <tbody class="divide-y">
                <tr v-for="u in recentUsers" :key="u.id">
                    <td class="px-6 py-3 font-mono text-gray-500">{{ u.id }}</td>
                    <td class="px-6 py-3 flex items-center space-x-2">
                        <img :src="u.avatar" class="w-6 h-6 rounded-full object-cover" />
                        <span>{{ u.name }}</span>
                    </td>
                    <td class="px-6 py-3">{{ u.email }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ ago(u.joined) }}</td>
                </tr>

                <tr v-if="!recentUsers.length">
                    <td colspan="4" class="px-6 py-6 text-center text-gray-500">
                        No users found.
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Recent transactions table -->
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <h2 class="text-lg font-semibold px-6 py-4 border-b">RecentTransactions</h2>

            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-3">#</th>
                    <th class="px-6 py-3">User</th>
                    <th class="px-6 py-3">Amount</th>
                    <th class="px-6 py-3">Date</th>
                </tr>
                </thead>
                <tbody class="divide-y">
                <tr v-for="t in recentTx" :key="t.id">
                    <td class="px-6 py-3 font-mono text-gray-500">{{ t.id }}</td>
                    <td class="px-6 py-3">{{ t.user }}</td>
                    <td class="px-6 py-3 font-semibold">{{ fmtCur(t.amount) }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ ago(t.date) }}</td>
                </tr>

                <tr v-if="!recentTx.length">
                    <td colspan="4" class="px-6 py-6 text-center text-gray-500">
                        No transactions found.
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import {getDashboardStats} from "../../services/DashboardService.js";

export default {
    name: 'DashboardView',
    data() {
        return {
            /* Replace with API payload */
            stats: {
                totalUsers  : 5_892,
                activeUsers :    73,
            },
            recentUsers: [
                { id: 1,  name: 'Alice', email: 'alice@example.com', avatar: 'https://i.pravatar.cc/40?u=1', joined: '2025-05-04T16:32:00Z' },
                /* …up to 10 users … */
            ],
            recentTx: [
                { id: 10101, user: 'Alice', amount: 299.99, date: '2025-05-04T16:45:00Z' },
                /* …up to 10 transactions … */
            ],
        }
    },
    methods: {
        fmtNum(n) { return new Intl.NumberFormat('en-IN').format(n) },
        fmtCur(v) {
            return new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
                maximumFractionDigits: 2
            }).format(v)
        },
        ago(iso) {
            const diff = (Date.now() - Date.parse(iso)) / 1000
            const m  = Math.floor(diff / 60)
            if (m < 60) return `${m}mago`
            const h = Math.floor(m / 60)
            if (h < 24) return `${h}hago`
            return `${Math.floor(h / 24)}dago`
        },

        async loadLive() {
            const r = await getDashboardStats()
            const { stats, users, transactions } = r.data
            this.stats       = stats
            this.recentUsers = users.slice(0, 10)
            this.recentTx    = transactions.slice(0, 10)
        }
    },
    created() {
        this.loadLive()
    }
}
</script>

<style scoped>
img { image-rendering: -webkit-optimize-contrast; }
</style>
