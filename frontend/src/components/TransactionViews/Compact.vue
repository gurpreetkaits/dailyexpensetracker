<div v-for="transaction in filteredTransactions" :key="transaction.id"
     class="bg-white rounded-lg p-2 shadow-sm hover:shadow-md transition-all"
     @click="editTransaction(transaction)">
  <div class="flex items-center justify-between">
    
    <!-- Icon and Category/Note in One Line -->
    <div class="flex items-center gap-2">
      <div class="w-4 h-4 rounded-full flex items-center justify-center" :style="{
        backgroundColor: transaction.type === 'income' ? '#e6ffed' : '#ffeded',
        color: transaction.type === 'income' ? '#16a34a' : '#dc2626'
      }">
        <component
          :is="transaction.category?.icon || (transaction.type === 'income' ? 'CircleDollarSign' : 'ShoppingBag')"
          class="h-4 w-4" />
      </div>
      <span class="text-sm font-medium text-gray-700 truncate">
        {{ transaction.category ? capitalizeFirstLetter(transaction.category.name) : transaction.note }}
      </span>
    </div>

    <!-- Amount Only -->
    <p class="text-sm font-semibold" :class="transaction.type === 'expense' ? 'text-red-500' : 'text-green-500'">
      {{ transaction.type === 'expense' ? '-' : '+' }}{{ formatCurrency(transaction.amount, currencyCode) }}
    </p>
  </div>
</div>
