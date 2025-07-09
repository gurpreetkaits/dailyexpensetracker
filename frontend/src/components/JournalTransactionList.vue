<template>
  <div>
    <!-- Search Bar -->
    <div class="mb-3 flex items-center gap-2">
      <input
        type="text"
        v-model="searchQuery"
        @input="debounceSearch"
        placeholder="Search notes..."
        class="flex-1 px-3 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400"
      />
      <RefreshCcw v-if="loading" class="h-4 w-4 animate-spin text-gray-500" />
    </div>

    <!-- Journal List -->
    <ul class="divide-y divide-gray-100 bg-white rounded-xl shadow-sm">
      <!-- Plus Row -->
      <li
        v-if="!isAdding"
        @click="startAdding"
        class="flex items-center justify-center py-3 cursor-pointer hover:bg-gray-50 group"
      >
        <Plus class="h-5 w-5 text-emerald-500 group-hover:scale-110 transition-transform" />
      </li>

      <!-- Add Form -->
      <li v-else class="py-3 px-2 flex items-center gap-2 bg-gray-50/50">
        <input type="date" v-model="newTx.date" class="w-32 text-sm border-gray-300 rounded-md" />
        <select v-model="newTx.type" class="w-24 text-sm border-gray-300 rounded-md">
          <option value="expense">Expense</option>
          <option value="income">Income</option>
        </select>
        <input
          v-model="newTx.note"
          placeholder="Note"
          class="flex-1 text-sm border-gray-300 rounded-md px-2 py-1"
        />
        <select v-model="newTx.category_id" class="w-28 text-sm border-gray-300 rounded-md">
          <option :value="null">None</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
        <input
          v-model.number="newTx.amount"
          type="number"
          step="0.01"
          placeholder="Amount"
          class="w-28 text-sm border-gray-300 rounded-md px-2 py-1"
        />
        <button @click="saveTx" class="text-emerald-600 text-sm font-medium px-2">Save</button>
        <button @click="cancelAdding" class="text-gray-500 text-sm px-2">Cancel</button>
      </li>

      <!-- Transaction Rows -->
      <li
        v-for="(tx, idx) in displayedTransactions"
        :key="tx.id"
        class="py-2 px-2 flex items-center gap-2 text-sm hover:bg-gray-50/50"
      >
        <!-- Date -->
        <span class="w-24 text-gray-500">{{ formatDate(tx.transaction_date) }}</span>
        <!-- Note -->
        <span class="flex-1 truncate">{{ tx.note || (tx.category ? capitalize(tx.category.name) : '-') }}</span>
        <!-- Amount -->
        <span
          class="w-24 text-right"
          :class="tx.type === 'expense' ? 'text-red-600' : 'text-green-600'"
        >
          {{ tx.type === 'expense' ? '-' : '+' }}{{ formatCurrency(tx.amount, currencyCode) }}
        </span>
        <!-- Running Balance (hidden on mobile) -->
        <span class="w-28 text-right text-gray-700 font-medium hidden sm:block">
          {{ formatCurrency(runningBalances[idx], currencyCode) }}
        </span>
      </li>
    </ul>
  </div>
</template>

<script>
import { Plus, RefreshCcw } from 'lucide-vue-next'
import { useTransactionStore } from '../store/transaction'
import { useCategoryStore } from '../store/category'
import { useWalletStore } from '../store/wallet'
import { mapActions, mapState } from 'pinia'
import { numberMixin } from '../mixins/numberMixin'

export default {
  name: 'JournalTransactionList',
  mixins: [numberMixin],
  components: { Plus, RefreshCcw },
  props: {
    transactions: {
      type: Array,
      required: true
    },
    currencyCode: {
      type: String,
      default: 'USD'
    }
  },
  data() {
    return {
      isAdding: false,
      newTx: {
        date: new Date().toISOString().slice(0, 10),
        note: '',
        amount: null,
        type: 'expense',
        category_id: null
      },
      searchQuery: '',
      searchTimeout: null,
      loading: false
    }
  },
  computed: {
    ...mapState(useCategoryStore, ['categories']),
    ...mapState(useWalletStore, ['wallets']),
    sortedTransactions() {
      // Sort by date ascending then id
      return [...this.transactions].sort((a, b) => new Date(a.transaction_date) - new Date(b.transaction_date))
    },
    runningBalances() {
      let balance = 0
      return this.sortedTransactions.map(tx => {
        balance += tx.type === 'income' ? Number(tx.amount) : -Number(tx.amount)
        return balance
      })
    },
    displayedTransactions() {
      if (!this.searchQuery.trim()) return this.sortedTransactions
      const q = this.searchQuery.toLowerCase()
      return this.sortedTransactions.filter(tx => (tx.note || '').toLowerCase().includes(q))
    }
  },
  methods: {
    ...mapActions(useTransactionStore, ['addTransaction', 'fetchPaginatedTransactions']),
    startAdding() {
      this.isAdding = true
      this.$nextTick(() => {
        // Focus note input
        const noteInput = this.$el.querySelector('input[placeholder="Note"]')
        noteInput && noteInput.focus()
      })
    },
    cancelAdding() {
      this.isAdding = false
      this.resetForm()
    },
    async saveTx() {
      // Validation minimal
      if (!this.newTx.amount) return
      const walletId = this.wallets.length ? this.wallets[0].id : null
      const payload = {
        type: this.newTx.type,
        amount: this.newTx.amount,
        note: this.newTx.note,
        category_id: this.newTx.category_id,
        transaction_date: this.newTx.date,
        wallet_id: walletId
      }
      const { success } = await this.addTransaction(payload)
      if (success) {
        this.isAdding = false
        this.resetForm()
        // Refresh paginated list to get accurate order
        await this.fetchPaginatedTransactions()
      }
    },
    resetForm() {
      this.newTx = {
        date: new Date().toISOString().slice(0, 10),
        note: '',
        amount: null,
        type: 'expense',
        category_id: null
      }
    },
    debounceSearch() {
      if (this.searchTimeout) clearTimeout(this.searchTimeout)
      this.searchTimeout = setTimeout(this.applySearch, 400)
    },
    async applySearch() {
      // Server-side search via store filters for scalability
      this.loading = true
      try {
        await this.fetchPaginatedTransactions(1, { search: this.searchQuery })
      } finally {
        this.loading = false
      }
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('en-IN', {
        month: 'short',
        day: 'numeric'
      })
    },
    capitalize(str) {
      if (!str) return ''
      return str.charAt(0).toUpperCase() + str.slice(1)
    }
  }
}
</script>

<style scoped>
/***** Mobile adjustments *****/
@media (max-width: 640px) {
  .w-24 {
    width: auto;
  }
  .hidden.sm\:block {
    display: none;
  }
}
</style> 