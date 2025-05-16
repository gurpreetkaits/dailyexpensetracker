<script>
import { numberMixin } from '../mixins/numberMixin'
import { iconMixin } from '../mixins/iconMixin'
import BottomSheet from './BottomSheet.vue'
import AddTransaction from './AddTransaction.vue'
import RecurringExpenseForm from './RecurringExpenseForm.vue'
import { useTransactionStore } from '../store/transaction'
import { mapActions, mapState } from 'pinia'
import { useSettingsStore } from '../store/settings'
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { useRecurringExpenseStore } from '../store/recurringExpense'

export default {
  name: 'ExpenseList',
  mixins: [numberMixin, iconMixin],
  components: {
    BottomSheet,
    AddTransaction,
    RecurringExpenseForm,
    Dialog,
    DialogPanel,
    TransitionRoot,
    TransitionChild
  },
  // ... rest of the code ...
}
</script> 