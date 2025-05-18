<template>
  <div>
    <GlobalModal v-if="hasActiveSubscription" :model-value="isVisible" @update:model-value="updateVisibility" title="Import Transactions" size="max-w-2xl">
      <div class="space-y-4">
        <!-- File Upload Section -->
        <div v-if="!excelData" class="border-2 border-dashed border-gray-200 rounded-lg p-8 text-center">
          <input
            type="file"
            ref="fileInput"
            accept=".xlsx,.xls,.csv"
            class="hidden"
            @change="handleFileUpload"
          />
          <div class="space-y-2">
            <Upload class="h-12 w-12 mx-auto text-gray-400" />
            <div class="text-sm text-gray-600">
              <button
                @click="$refs.fileInput.click()"
                class="text-emerald-500 hover:text-emerald-600 font-medium"
              >
                Click to upload
              </button>
              or drag and drop
            </div>
            <p class="text-xs text-gray-500">Excel or CSV files only</p>
          </div>
        </div>

        <!-- Column Mapping Section -->
        <div v-else class="space-y-4">
          <div class="text-sm text-gray-600">
            Map your Excel columns to our database fields. Required fields are marked with *
          </div>
          
          <div class="space-y-3">
            <div v-for="(mapping, index) in columnMappings" :key="index" class="flex items-center gap-3">
              <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  {{ mapping.required ? mapping.label + ' *' : mapping.label }}
                </label>
                <select
                  v-model="mapping.selectedColumn"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                  :class="{ 'border-red-300': mapping.required && !mapping.selectedColumn }"
                >
                  <option value="">Select a column</option>
                  <option v-for="col in excelColumns" :key="col" :value="col">
                    {{ col }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <button
              @click="resetImport"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="startImport"
              :disabled="!isValidMapping"
              class="px-4 py-2 text-sm font-medium text-white bg-emerald-500 rounded-md hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Start Import
            </button>
          </div>
        </div>
      </div>
    </GlobalModal>

    <UpgradeModal
      v-else
      :model-value="isVisible"
      @update:model-value="updateVisibility"
      @upgrade="handleUpgrade"
    />
  </div>
</template>

<script>
import { Upload } from 'lucide-vue-next'
import GlobalModal from '../Global/GlobalModal.vue'
import UpgradeModal from '../UpgradeModal.vue'
import { useNotifications } from '../../composables/useNotifications'
import { useSubscriptionStore } from '../../store/subscription'
import * as XLSX from 'xlsx'
import { usePolarStore } from '../../store/polar'

export default {
  name: 'ImportTransactionsModal',
  components: {
    GlobalModal,
    Upload,
    UpgradeModal
  },
  props: {
    modelValue: {
      type: Boolean,
      required: true
    }
  },
  emits: ['update:modelValue', 'import-started'],
  data() {
    return {
      excelData: null,
      excelColumns: [],
      columnMappings: [
        { label: 'Transaction Date', field: 'transaction_date', required: true, selectedColumn: '' },
        { label: 'Amount', field: 'amount', required: true, selectedColumn: '' },
        { label: 'Category', field: 'category', required: true, selectedColumn: '' },
        { label: 'Type', field: 'type', required: true, selectedColumn: '' },
        { label: 'Note', field: 'note', required: false, selectedColumn: '' }
      ]
    }
  },
  computed: {
    isVisible: {
      get() {
        return this.modelValue
      },
      set(value) {
        this.$emit('update:modelValue', value)
      }
    },
    isValidMapping() {
      return this.columnMappings.every(mapping => !mapping.required || mapping.selectedColumn)
    },
    hasActiveSubscription() {
      const subscriptionStore = usePolarStore()
      return subscriptionStore.hasActiveSubscription
    }
  },
  methods: {
    updateVisibility(value) {
      this.isVisible = value
    },
    handleUpgrade() {
      // This will be handled by the subscription store
      this.updateVisibility(false)
    },
    async handleFileUpload(event) {
      const file = event.target.files[0]
      if (!file) return

      try {
        const data = await this.readExcelFile(file)
        this.excelData = data
        this.excelColumns = Object.keys(data[0] || {})
      } catch (error) {
        const { notify } = useNotifications()
        notify({
          title: 'Error',
          message: 'Failed to read Excel file. Please make sure it\'s a valid Excel or CSV file.',
          type: 'error'
        })
      }
    },

    readExcelFile(file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader()
        reader.onload = (e) => {
          try {
            const data = e.target.result
            const workbook = XLSX.read(data, { type: 'array' })
            const firstSheetName = workbook.SheetNames[0]
            const worksheet = workbook.Sheets[firstSheetName]
            const jsonData = XLSX.utils.sheet_to_json(worksheet)
            resolve(jsonData)
          } catch (error) {
            reject(error)
          }
        }
        reader.onerror = reject
        reader.readAsArrayBuffer(file)
      })
    },

    resetImport() {
      this.excelData = null
      this.excelColumns = []
      this.columnMappings.forEach(mapping => mapping.selectedColumn = '')
      this.$emit('update:modelValue', false)
    },

    async startImport() {
      if (!this.isValidMapping) return

      const mapping = {}
      this.columnMappings.forEach(m => {
        if (m.selectedColumn) {
          mapping[m.field] = m.selectedColumn
        }
      })

      const { notify } = useNotifications()
      try {
        // Transform data according to mapping
        const transformedData = this.excelData.map(row => {
          const transformed = {}
          Object.entries(mapping).forEach(([field, column]) => {
            transformed[field] = row[column]
          })
          return transformed
        })

        // Emit event with transformed data
        this.$emit('import-started', transformedData)
        notify({
          title: 'Success',
          message: 'Import has been queued. Your transactions will appear shortly.',
          type: 'success'
        })
        this.resetImport()
      } catch (error) {
        notify({
          title: 'Error',
          message: 'Failed to start import. Please try again.',
          type: 'error'
        })
      }
    }
  }
}
</script> 