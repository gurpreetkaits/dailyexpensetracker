<template>
    <div class="p-6 ml-28">
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <h2 class="text-lg font-semibold px-6 py-4 border-b">{{ title }}</h2>

            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left uppercase text-xs tracking-wider">
                <tr>
                    <th v-for="column in columns" :key="column.key" class="px-6 py-3">{{ column.label }}</th>
                </tr>
                </thead>
                <tbody class="divide-y">
                <tr
                    v-for="row in paginatedData"
                    :key="row[idKey]"
                    class="cursor-pointer hover:bg-gray-50"
                    @click="onRowClick(row)"
                >
                    <td v-for="column in columns" :key="column.key" class="px-6 py-3" :class="column.class">
                        {{ row[column.key] }}
                    </td>
                </tr>
                <tr v-if="!paginatedData.length">
                    <td :colspan="columns.length" class="px-6 py-6 text-center text-gray-500">
                        No data available.
                    </td>
                </tr>
                </tbody>
            </table>

            <div class="flex justify-between items-center px-6 py-4 border-t">
                <button
                    class="px-3 py-1 rounded bg-gray-100 hover:bg-gray-200 disabled:opacity-50"
                    :disabled="currentPage === 1"
                    @click="goToPage(currentPage - 1)"
                >
                    Previous
                </button>

                <p class="text-sm text-gray-600">
                    Page {{ currentPage }} of {{ totalPages }}
                </p>

                <button
                    class="px-3 py-1 rounded bg-gray-100 hover:bg-gray-200 disabled:opacity-50"
                    :disabled="currentPage === totalPages"
                    @click="goToPage(currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'GlobalTable',
    props: {
        columns: {
            type: Array,
            required: true,
        },
        data: {
            type: Array,
            required: true,
        },
        pageSize: {
            type: Number,
            default: 10,
        },
        idKey: {
            type: String,
            default: 'id',
        },
        title: {
            type: String,
            default: 'Table'
        }
    },
    data() {
        return {
            currentPage: 1,
        }
    },
    computed: {
        totalPages() {
            return Math.ceil(this.data.length / this.pageSize)
        },
        paginatedData() {
            const start = (this.currentPage - 1) * this.pageSize
            return this.data.slice(start, start + this.pageSize)
        },
    },
    methods: {
        goToPage(page) {
            if (page < 1 || page > this.totalPages) return
            this.currentPage = page
            this.$emit('update:page', page)
        },
        onRowClick(row) {
            this.$emit('row-click', row)
        },
    },
}
</script>

<style scoped>
img { image-rendering: -webkit-optimize-contrast; }
</style>
