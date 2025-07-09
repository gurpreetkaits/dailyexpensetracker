<template>
  <div>
    <GlobalModal
      :model-value="isVisible"
      @update:model-value="updateVisibility"
      title="Import Bank Statement"
      size="max-w-4xl"
      :close-on-backdrop="false"
    >
      <div class="max-h-[80vh] overflow-y-auto">
        <div v-if="!fileUploaded" class="space-y-4">
          <!-- File Upload Section -->
          <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-emerald-400 transition-colors">
            <input
              ref="fileInput"
              type="file"
              accept=".xlsx,.xls,.csv"
              class="hidden"
              @change="handleFileUpload"
            />
            <div class="space-y-2">
              <Upload class="h-8 w-8 mx-auto text-gray-400" />
              <div class="text-sm text-gray-600">
                <button
                  @click="$refs.fileInput.click()"
                  class="text-emerald-600 hover:text-emerald-700 font-medium"
                >
                  Click to upload
                </button>
                or drag your bank statement
              </div>
              <p class="text-xs text-gray-500">Excel (.xlsx, .xls) or CSV • Max 10MB</p>
            </div>
          </div>

          <!-- Saved Mappings Section -->
          <div v-if="savedMappings.length > 0" class="bg-gray-50 rounded-lg p-3">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Mappings</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <div 
                v-for="mapping in savedMappings" 
                :key="mapping.id" 
                class="flex items-center justify-between p-2 bg-white rounded border transition-colors"
                :class="{
                  'border-emerald-300 bg-emerald-50': selectedMappingId === mapping.id,
                  'border-gray-200 hover:border-emerald-200': selectedMappingId !== mapping.id
                }"
              >
                <div class="flex-1 min-w-0">
                  <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-900 truncate block">{{ mapping.name }}</span>
                    <svg 
                      v-if="selectedMappingId === mapping.id" 
                      class="w-4 h-4 text-emerald-600 flex-shrink-0" 
                      fill="none" 
                      stroke="currentColor" 
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                  <p class="text-xs text-gray-500">{{ formatDate(mapping.created_at) }}</p>
                </div>
                <div class="flex items-center space-x-1 ml-2">
                  <button
                    @click="loadMapping(mapping)"
                    class="text-xs px-2 py-1 rounded transition-colors"
                    :class="{
                      'bg-emerald-200 text-emerald-800': selectedMappingId === mapping.id,
                      'bg-emerald-100 text-emerald-700 hover:bg-emerald-200': selectedMappingId !== mapping.id
                    }"
                  >
                    {{ selectedMappingId === mapping.id ? 'Active' : 'Use' }}
                  </button>
                  <button
                    @click="deleteMapping(mapping.id)"
                    class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200"
                  >
                    Del
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Column Mapping Section -->
        <div v-if="fileUploaded" class="space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Match your file columns to transaction fields</span>
            <button
              @click="resetImport"
              class="text-sm text-blue-600 hover:text-blue-700 underline"
            >
              Upload Different File
            </button>
          </div>

          <!-- Wallet Selection -->
          <div class="bg-gray-50 rounded-lg p-3">
            <label class="text-sm font-medium text-gray-700 mb-2 block">
              Select Wallet <span class="text-red-500">*</span>
            </label>
            <select
              v-model="selectedWalletId"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
              <option v-for="wallet in wallets" :key="wallet.id" :value="wallet.id">
                {{ wallet.name }}
              </option>
            </select>
          </div>

          <!-- Mapping Grid -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <!-- Transaction Date -->
            <div class="bg-gray-50 rounded-lg p-3">
              <label class="text-sm font-medium text-gray-700 mb-2 block">
                Transaction Date <span class="text-red-500">*</span>
              </label>
              <select
                v-model="transactionFields.find(f => f.field === 'transaction_date').selectedColumn"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-emerald-500"
              >
                <option value="">Select Column</option>
                <option v-for="column in fileColumns" :key="column" :value="column">
                  {{ column }}
                </option>
              </select>
            </div>

            <!-- Amount -->
            <div class="bg-gray-50 rounded-lg p-3">
              <label class="text-sm font-medium text-gray-700 mb-2 block">
                Amount <span class="text-red-500">*</span>
              </label>
              <select
                v-model="transactionFields.find(f => f.field === 'amount').selectedColumn"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-emerald-500"
              >
                <option value="">Select Column</option>
                <option v-for="column in fileColumns" :key="column" :value="column">
                  {{ column }}
                </option>
              </select>
            </div>

            <!-- Transaction Type -->
            <div class="bg-gray-50 rounded-lg p-3">
              <label class="text-sm font-medium text-gray-700 mb-2 block">
                Transaction Type <span class="text-red-500">*</span>
              </label>
              <div class="space-y-2">
                <div class="flex items-center space-x-3">
                  <label class="flex items-center cursor-pointer">
                    <input
                      type="radio"
                      :value="'income'"
                      v-model="typeField.defaultType"
                      @change="typeField.useMapping = false"
                      class="mr-1"
                    />
                    <span class="text-xs text-green-700">Income</span>
                  </label>
                  <label class="flex items-center cursor-pointer">
                    <input
                      type="radio"
                      :value="'expense'"
                      v-model="typeField.defaultType"
                      @change="typeField.useMapping = false"
                      class="mr-1"
                    />
                    <span class="text-xs text-red-700">Expense</span>
                  </label>
                  <label class="flex items-center cursor-pointer">
                    <input
                      type="radio"
                      :value="true"
                      v-model="typeField.useMapping"
                      @change="typeField.defaultType = ''"
                      class="mr-1"
                    />
                    <span class="text-xs text-gray-600">From File</span>
                  </label>
                </div>
                
                <select
                  v-if="typeField.useMapping"
                  v-model="typeField.selectedColumn"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
                  <option value="">Select Column</option>
                  <option v-for="column in fileColumns" :key="column" :value="column">
                    {{ column }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Category -->
            <div class="bg-gray-50 rounded-lg p-3">
              <label class="text-sm font-medium text-gray-700 mb-2 block">Category</label>
              <div class="space-y-2">
                <div class="flex items-center space-x-2">
                  <label class="flex items-center cursor-pointer">
                    <input
                      type="radio"
                      :value="'mapping'"
                      v-model="categoryField.mode"
                      class="mr-1"
                    />
                    <span class="text-xs text-gray-600">From File</span>
                  </label>
                  <label class="flex items-center cursor-pointer">
                    <input
                      type="radio"
                      :value="'ai'"
                      v-model="categoryField.mode"
                      class="mr-1"
                    />
                    <span class="text-xs text-purple-600">AI Decide</span>
                  </label>
                </div>
                
                <!-- File Column Selection -->
                <select
                  v-if="categoryField.mode === 'mapping'"
                  v-model="categoryField.selectedColumn"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
                  <option value="">Select Column</option>
                  <option v-for="column in fileColumns" :key="column" :value="column">
                    {{ column }}
                  </option>
                </select>
                
                <!-- AI Mode Info -->
                <p v-if="categoryField.mode === 'ai'" class="text-xs text-purple-600">
                  AI will analyze transaction descriptions to assign categories
                </p>
              </div>
            </div>

            <!-- Description/Note -->
            <div class="bg-gray-50 rounded-lg p-3">
              <label class="text-sm font-medium text-gray-700 mb-2 block">Description/Note</label>
              <select
                v-model="transactionFields.find(f => f.field === 'note').selectedColumn"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-emerald-500"
              >
                <option value="">Select Column</option>
                <option v-for="column in fileColumns" :key="column" :value="column">
                  {{ column }}
                </option>
              </select>
            </div>

            <!-- Reference Number -->
            <div class="bg-gray-50 rounded-lg p-3">
              <label class="text-sm font-medium text-gray-700 mb-2 block">Reference Number</label>
              <select
                v-model="transactionFields.find(f => f.field === 'reference_number').selectedColumn"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-emerald-500"
              >
                <option value="">Select Column</option>
                <option v-for="column in fileColumns" :key="column" :value="column">
                  {{ column }}
                </option>
              </select>
            </div>
          </div>

          <!-- Save Mapping Section -->
          <div class="bg-blue-50 rounded-lg p-3">
            <div class="flex items-center space-x-2">
              <input
                v-model="mappingName"
                type="text"
                placeholder="Save mapping as... (e.g., 'HDFC Bank')"
                class="flex-1 px-3 py-2 text-sm border border-blue-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
              <button
                @click="saveMapping"
                :disabled="!mappingName || !isValidMapping"
                class="px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
              >
                Save
              </button>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-8">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-emerald-500 mb-3"></div>
          <span class="text-sm text-gray-600">{{ loadingMessage }}</span>
        </div>
      </div>

      <!-- Footer -->
      <template #footer>
        <div v-if="fileUploaded && !loading" class="flex justify-between items-center">
          <div class="text-sm text-gray-500">
            {{ fileColumns.length }} columns detected
          </div>
          <div class="flex space-x-2">
            <button
              @click="resetImport"
              class="px-3 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 text-sm"
            >
              Cancel
            </button>
            <button
              @click="importTransactions"
              :disabled="!isValidMapping"
              class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-sm"
            >
              Import Transactions
            </button>
          </div>
        </div>
      </template>
    </GlobalModal>

    <!-- Results Modal -->
    <GlobalModal
      :model-value="showResults"
      @update:model-value="showResults = $event"
      title="Import Complete"
      size="max-w-md"
    >
      <div class="text-center space-y-4">
        <div class="flex justify-center">
          <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
        </div>

        <div>
          <div class="text-2xl font-bold text-emerald-600">{{ importResults.imported_count }}</div>
          <div class="text-sm text-gray-600">transactions imported</div>
        </div>

        <div v-if="importResults.failed_count > 0" class="bg-red-50 rounded-lg p-3">
          <div class="text-lg font-semibold text-red-600">{{ importResults.failed_count }} failed</div>
          <div class="text-xs text-red-600">Some transactions couldn't be imported</div>
        </div>

        <div v-if="importResults.errors && importResults.errors.length > 0" class="text-left">
          <details class="bg-gray-50 rounded-lg p-3">
            <summary class="cursor-pointer text-xs font-medium text-gray-700 hover:text-gray-900">
              View Errors
            </summary>
            <div class="mt-2 space-y-1 max-h-32 overflow-y-auto">
              <div v-for="error in importResults.errors" :key="error" class="text-xs text-red-600 bg-red-50 p-2 rounded">
                {{ error }}
              </div>
            </div>
          </details>
        </div>
      </div>

      <template #footer>
        <button
          @click="closeResults"
          class="w-full px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700"
        >
          Done
        </button>
      </template>
    </GlobalModal>
  </div>
</template>

<script>
import { Upload } from 'lucide-vue-next'
import GlobalModal from './Global/GlobalModal.vue'
import { useNotifications } from '../composables/useNotifications'
import { useWalletStore } from '../store/wallet'
import * as ImportService from '../services/ImportService'

export default {
  name: 'ImportModal',
  components: {
    GlobalModal,
    Upload
  },
  props: {
    modelValue: {
      type: Boolean,
      required: true
    }
  },
  emits: ['update:modelValue', 'import-completed'],
  data() {
    return {
      fileUploaded: false,
      fileColumns: [],
      filePath: '',
      mappingName: '',
      savedMappings: [],
      selectedWalletId: '',
      loading: false,
      loadingMessage: '',
      showResults: false,
      importResults: {},
      transactionFields: [
        { label: 'Transaction Date', field: 'transaction_date', required: true, selectedColumn: '' },
        { label: 'Amount', field: 'amount', required: true, selectedColumn: '' },
        { label: 'Description/Note', field: 'note', required: false, selectedColumn: '' },
        { label: 'Reference Number', field: 'reference_number', required: false, selectedColumn: '' }
      ],
      typeField: {
        useMapping: false,
        defaultType: 'expense',
        selectedColumn: ''
      },
      categoryField: {
        mode: 'mapping', // 'mapping', 'ai'
        selectedColumn: ''
      },
      selectedMappingId: null
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
      // Check required fields
      const dateField = this.transactionFields.find(f => f.field === 'transaction_date')
      const amountField = this.transactionFields.find(f => f.field === 'amount')
      
      const hasDate = dateField && dateField.selectedColumn
      const hasAmount = amountField && amountField.selectedColumn
      const hasType = !this.typeField.useMapping || this.typeField.selectedColumn
      const hasWallet = this.selectedWalletId
      
      return hasDate && hasAmount && hasType && hasWallet
    },
    wallets() {
      return useWalletStore().wallets
    }
  },
  watch: {
    modelValue(newValue) {
      if (newValue) {
        this.loadSavedMappings()
        this.loadWallets()
      }
    }
  },
  methods: {
    updateVisibility(value) {
      this.isVisible = value
    },
    
    async loadWallets() {
      const walletStore = useWalletStore()
      if (walletStore.wallets.length === 0) {
        await walletStore.fetchWallets()
      }
      // Select first wallet by default
      if (walletStore.wallets.length > 0 && !this.selectedWalletId) {
        this.selectedWalletId = walletStore.wallets[0].id
      }
    },

    async handleFileUpload(event) {
      const file = event.target.files[0]
      if (!file) return

      this.loading = true
      this.loadingMessage = 'Reading file...'

      try {
        const response = await ImportService.uploadFile(file)

        if (response.success) {
          this.fileColumns = response.headings
          this.filePath = response.file_path
          this.fileUploaded = true
        } else {
          throw new Error(response.message || 'Failed to upload file')
        }
      } catch (error) {
        const { notify } = useNotifications()
        notify({
          title: 'Error',
          message: error.message || 'Failed to read file. Please ensure it\'s a valid Excel or CSV file.',
          type: 'error'
        })
      } finally {
        this.loading = false
      }
    },

    async loadSavedMappings() {
      try {
        const response = await ImportService.getMappings()
        if (response.success) {
          this.savedMappings = response.mappings
        }
      } catch (error) {
        console.error('Failed to load saved mappings:', error)
      }
    },

    loadMapping(mapping) {
      const mappings = mapping.mappings
      
      // Load regular fields
      this.transactionFields.forEach(field => {
        const savedMapping = mappings.find(m => m.field === field.field)
        if (savedMapping) {
          field.selectedColumn = savedMapping.column
        }
      })
      
      // Load type field
      const typeMapping = mappings.find(m => m.field === 'type')
      if (typeMapping) {
        this.typeField.useMapping = typeMapping.useMapping || false
        this.typeField.defaultType = typeMapping.defaultType || 'expense'
        this.typeField.selectedColumn = typeMapping.column || ''
      }
      
      // Load category field
      const categoryMapping = mappings.find(m => m.field === 'category')
      if (categoryMapping) {
        this.categoryField.mode = categoryMapping.mode || 'mapping'
        this.categoryField.selectedColumn = categoryMapping.column || ''
      }
      
      // Load wallet field
      const walletMapping = mappings.find(m => m.field === 'wallet')
      if (walletMapping && walletMapping.wallet_id) {
        this.selectedWalletId = walletMapping.wallet_id
      }

      this.selectedMappingId = mapping.id
    },

    async saveMapping() {
      if (!this.mappingName || !this.isValidMapping) return

      try {
        const mappings = [
          ...this.transactionFields.map(field => ({
            field: field.field,
            column: field.selectedColumn || ''
          })),
          {
            field: 'type',
            useMapping: this.typeField.useMapping,
            defaultType: this.typeField.defaultType,
            column: this.typeField.selectedColumn || ''
          },
          {
            field: 'category',
            mode: this.categoryField.mode,
            column: this.categoryField.selectedColumn || ''
          },
          {
            field: 'wallet',
            wallet_id: this.selectedWalletId
          }
        ]

        const response = await ImportService.saveMappings(this.mappingName, mappings)
        
        if (response.success) {
          const { notify } = useNotifications()
          notify({
            title: 'Success',
            message: 'Mapping saved successfully',
            type: 'success'
          })
          this.mappingName = ''
          await this.loadSavedMappings()
          // Set the newly saved mapping as selected
          if (response.mapping && response.mapping.id) {
            this.selectedMappingId = response.mapping.id
          }
        }
      } catch (error) {
        const { notify } = useNotifications()
        notify({
          title: 'Error',
          message: 'Failed to save mapping',
          type: 'error'
        })
      }
    },

    async deleteMapping(id) {
      try {
        const response = await ImportService.deleteMapping(id)

        if (response.success) {
          const { notify } = useNotifications()
          notify({
            title: 'Success',
            message: 'Mapping deleted successfully',
            type: 'success'
          })
          // Reset selected mapping if it was the one being deleted
          if (this.selectedMappingId === id) {
            this.selectedMappingId = null
          }
          this.loadSavedMappings()
        }
      } catch (error) {
        const { notify } = useNotifications()
        notify({
          title: 'Error',
          message: 'Failed to delete mapping',
          type: 'error'
        })
      }
    },

    async importTransactions() {
      if (!this.isValidMapping) return

      this.loading = true
      this.loadingMessage = 'Importing transactions...'

      try {
        const mappings = [
          ...this.transactionFields.map(field => ({
            field: field.field,
            column: field.selectedColumn || ''
          })),
          {
            field: 'type',
            useMapping: this.typeField.useMapping,
            defaultType: this.typeField.defaultType,
            column: this.typeField.selectedColumn || ''
          },
          {
            field: 'category',
            mode: this.categoryField.mode,
            column: this.categoryField.selectedColumn || ''
          },
          {
            field: 'wallet',
            wallet_id: this.selectedWalletId
          }
        ]

        const response = await ImportService.importTransactions(this.filePath, mappings)
        
        if (response.success) {
          this.importResults = response
          this.showResults = true
          this.resetImport()
          this.$emit('import-completed', {
            imported_count: response.imported_count,
            failed_count: response.failed_count,
            errors: response.errors
          })
        }
      } catch (error) {
        const { notify } = useNotifications()
        notify({
          title: 'Error',
          message: error.message || 'Failed to import transactions',
          type: 'error'
        })
      } finally {
        this.loading = false
      }
    },

    resetImport() {
      this.fileUploaded = false
      this.fileColumns = []
      this.filePath = ''
      this.mappingName = ''
      this.selectedMappingId = null
      this.transactionFields.forEach(field => {
        field.selectedColumn = ''
      })
      this.typeField = {
        useMapping: false,
        defaultType: 'expense',
        selectedColumn: ''
      }
      this.categoryField = {
        mode: 'mapping',
        selectedColumn: ''
      }
      // Reset wallet selection to first wallet
      if (this.wallets.length > 0) {
        this.selectedWalletId = this.wallets[0].id
      }
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = ''
      }
    },

    closeResults() {
      this.showResults = false
      this.importResults = {}
      this.isVisible = false
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      })
    }
  }
}
</script>
