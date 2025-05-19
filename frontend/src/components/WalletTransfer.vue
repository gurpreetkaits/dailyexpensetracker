<template>
  <div class="p-4">
    <form @submit.prevent="handleTransfer" class="space-y-4">
      <!-- From Wallet -->
      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">From Wallet</label>
        <select
          v-model="transferForm.fromWalletId"
          class="w-full p-2.5 text-sm border rounded-md bg-gray-50 focus:bg-white
                 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
          required
          :disabled="isSubmitting"
        >
          <option value="" disabled>Select source wallet</option>
          <option
            v-for="wallet in availableWallets"
            :key="wallet.id"
            :value="wallet.id"
            :disabled="wallet.balance <= 0"
            class="py-1"
          >
            {{ wallet.name }} (Balance: {{ formatCurrency(wallet.balance) }})
          </option>
        </select>
      </div>

      <!-- To Wallet -->
      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">To Wallet</label>
        <select
          v-model="transferForm.toWalletId"
          class="w-full p-2.5 text-sm border rounded-md bg-gray-50 focus:bg-white
                 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
          required
          :disabled="isSubmitting"
        >
          <option value="" disabled>Select destination wallet</option>
          <option
            v-for="wallet in availableWallets"
            :key="wallet.id"
            :value="wallet.id"
            :disabled="wallet.id === transferForm.fromWalletId"
            class="py-1"
          >
            {{ wallet.name }} (Balance: {{ formatCurrency(wallet.balance) }})
          </option>
        </select>
      </div>

      <!-- Amount -->
      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Amount</label>
        <div class="relative">
          <DollarSign class="absolute left-2.5 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input
            v-model="transferForm.amount"
            type="number"
            step="0.01"
            min="0.01"
            :max="selectedFromWallet?.balance || 0"
            class="w-full p-2.5 pl-9 text-sm border rounded-md bg-gray-50 focus:bg-white
                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            placeholder="0.00"
            required
            :disabled="isSubmitting"
          />
        </div>
        <p v-if="selectedFromWallet" class="text-xs text-gray-500">
          Available balance: {{ formatCurrency(selectedFromWallet.balance) }}
        </p>
      </div>

      <!-- Note -->
      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Note (Optional)</label>
        <input
          v-model="transferForm.note"
          type="text"
          class="w-full p-2.5 text-sm border rounded-md bg-gray-50 focus:bg-white
                 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
          placeholder="Add a note..."
          :disabled="isSubmitting"
        />
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end gap-2 pt-2">
        <button
          type="button"
          @click="$emit('close')"
          class="px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md"
          :disabled="isSubmitting"
        >
          Cancel
        </button>
        <button
          type="submit"
          class="px-3 py-1.5 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600
                 disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="!isValid || isSubmitting"
        >
          {{ isSubmitting ? 'Transferring...' : 'Transfer' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { DollarSign } from 'lucide-vue-next'
import { useWalletStore } from '../store/wallet'
import { useNotifications } from '../composables/useNotifications'

const props = defineProps({
  wallets: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['close', 'transfer-complete'])
const walletStore = useWalletStore()
const { notify } = useNotifications()

const isSubmitting = ref(false)
const transferForm = ref({
  fromWalletId: '',
  toWalletId: '',
  amount: '',
  note: ''
})

const availableWallets = computed(() => props.wallets)

const selectedFromWallet = computed(() => 
  availableWallets.value.find(w => w.id === transferForm.value.fromWalletId)
)

const isValid = computed(() => {
  return transferForm.value.fromWalletId &&
         transferForm.value.toWalletId &&
         transferForm.value.amount > 0 &&
         transferForm.value.fromWalletId !== transferForm.value.toWalletId &&
         (!selectedFromWallet.value || transferForm.value.amount <= selectedFromWallet.value.balance)
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const handleTransfer = async () => {
  if (!isValid.value || isSubmitting.value) return

  isSubmitting.value = true
  try {
    await walletStore.transferBetweenWallets({
      fromWalletId: transferForm.value.fromWalletId,
      toWalletId: transferForm.value.toWalletId,
      amount: parseFloat(transferForm.value.amount),
      note: transferForm.value.note || 'Wallet transfer'
    })

    notify({
      title: 'Success',
      message: 'Transfer completed successfully',
      type: 'success'
    })

    emit('transfer-complete')
    emit('close')
  } catch (error) {
    notify({
      title: 'Error',
      message: error.message || 'Failed to complete transfer',
      type: 'error'
    })
  } finally {
    isSubmitting.value = false
  }
}
</script> 