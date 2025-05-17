<template>
  <div class="max-w-4xl mx-auto p-4 mb-10">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-4">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <template v-else>
      <!-- Header with Add Button -->
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold text-gray-900">Categories</h1>
        <button @click="showAddModal = true"
                class="hidden md:flex bg-emerald-500 text-white px-3 py-1.5 rounded-lg hover:bg-emerald-600 transition-colors items-center gap-1.5 text-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add
        </button>
      </div>

      <!-- Categories List -->
      <div v-if="categories && categories.length > 0" class="bg-white rounded-lg shadow divide-y divide-gray-200">
        <div v-for="category in categories" :key="category.id"
             class="py-2 px-3 flex items-center justify-between hover:bg-gray-50">
          <div class="flex items-center gap-2">
            <div
              class="h-7 w-7 rounded-full flex items-center justify-center"
              :style="{
                backgroundColor: `${category.color}`,
                color: category.color
              }"
            >
              <span class="text-lg">
                {{ getCategoryEmoji(category.icon) }}
                <span style="display:none">{{ console.log('category.icon in list:', category.icon) }}</span>
              </span>
            </div>
            <div>
              <div class="font-medium text-gray-900 text-sm">{{ category.name }}</div>
              <div class="flex items-center gap-1.5 text-xs">
                <span class="px-1.5 py-0.5 rounded-full font-medium"
                      :class="category.type === 'expense' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'">
                  {{ category.type }}
                </span>
                <span class="text-gray-500">{{ category.transaction_count || 0 }} transactions</span>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-1">
            <button @click="editCategory(category)"
                    class="p-1 text-gray-400 hover:text-blue-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button @click="deleteCategory(category)"
                    class="p-1 text-gray-400 hover:text-red-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-8">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No categories</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new category.</p>
        <div class="mt-4">
          <button @click="showAddModal = true"
                  class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-emerald-500 hover:bg-emerald-600">
            Add Category
          </button>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="categories && categories.length > 0 && totalPages > 1" class="mt-4 flex items-center justify-between">
        <div class="text-sm text-gray-500">
          Showing {{ startIndex }}-{{ endIndex }} of {{ totalItems }}
        </div>
        <div class="flex gap-1">
          <button @click="handlePageChange(currentPage - 1)"
                  :disabled="currentPage === 1"
                  class="px-2 py-1 rounded border text-sm disabled:opacity-50"
                  :class="currentPage === 1 ? 'text-gray-400' : 'text-gray-700 hover:bg-gray-50'">
            Previous
          </button>
          <button v-for="page in displayedPages"
                  :key="page"
                  @click="handlePageChange(page)"
                  class="px-2 py-1 rounded border text-sm"
                  :class="currentPage === page ? 'bg-emerald-500 text-white border-emerald-500' : 'text-gray-700 hover:bg-gray-50'">
            {{ page }}
          </button>
          <button @click="handlePageChange(currentPage + 1)"
                  :disabled="currentPage === totalPages"
                  class="px-2 py-1 rounded border text-sm disabled:opacity-50"
                  :class="currentPage === totalPages ? 'text-gray-400' : 'text-gray-700 hover:bg-gray-50'">
            Next
          </button>
        </div>
      </div>

      <!-- Floating Action Button (Mobile) -->
      <button @click="showAddModal = true"
        class="md:hidden fixed bottom-[80px] left-1/2 transform -translate-x-1/2 inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-500 text-white shadow-xl hover:bg-emerald-600 hover:shadow-2xl transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
      </button>

      <!-- Add/Edit Category Modal (Desktop) -->
      <GlobalModal
        v-model="showAddModal"
        :title="editingCategory ? 'Edit Category' : 'Add Category'"
        v-if="!isMobile"
      >
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" v-model="categoryForm.name"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                   required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Type</label>
            <select v-model="categoryForm.type"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
              <option value="expense">Expense</option>
              <option value="income">Income</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Color</label>
            <div class="mt-1 flex items-center gap-2">
              <input type="color" v-model="categoryForm.color"
                     class="h-8 w-8 rounded cursor-pointer"
                     title="Choose color">
              <span class="text-sm text-gray-500">{{ categoryForm.color }}</span>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Icon</label>
            <div class="grid grid-cols-6 gap-2 max-h-48 overflow-y-auto p-1">
              <button v-for="(emoji, name) in availableEmojis"
                      :key="name"
                      type="button"
                      @click="selectIcon(name)"
                      class="h-10 w-10 rounded-lg flex items-center justify-center hover:bg-gray-100 transition-colors text-lg"
                      :class="categoryForm.icon === name ? 'bg-emerald-50 text-emerald-600 ring-2 ring-emerald-500' : 'text-gray-600'">
                {{ emoji }}
              </button>
            </div>
          </div>
        </form>
        <template #footer>
          <button @click="closeModal"
                  class="px-3 py-1.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
            Cancel
          </button>
          <button @click="handleSubmit"
                  class="ml-3 px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-500 hover:bg-emerald-600"
                  :disabled="isSubmitting || !categoryForm.name.trim()">
            {{ isSubmitting ? 'Saving...' : 'Save' }}
          </button>
        </template>
      </GlobalModal>

      <!-- Add/Edit Category Bottom Sheet (Mobile) -->
      <BottomSheet v-model="showAddModal" v-if="isMobile">
        <div class="p-4">
          <h3 class="text-lg font-semibold mb-4">{{ editingCategory ? 'Edit Category' : 'Add Category' }}</h3>
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name</label>
              <input type="text" v-model="categoryForm.name"
                     class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                     required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Type</label>
              <select v-model="categoryForm.type"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="expense">Expense</option>
                <option value="income">Income</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Color</label>
              <div class="mt-1 flex items-center gap-2">
                <input type="color" v-model="categoryForm.color"
                       class="h-8 w-8 rounded cursor-pointer"
                       title="Choose color">
                <span class="text-sm text-gray-500">{{ categoryForm.color }}</span>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Icon</label>
              <div class="grid grid-cols-6 gap-2 max-h-48 overflow-y-auto p-1">
                <button v-for="(emoji, name) in availableEmojis"
                        :key="name"
                        type="button"
                        @click="selectIcon(name)"
                        class="h-10 w-10 rounded-lg flex items-center justify-center hover:bg-gray-100 transition-colors text-lg"
                        :class="categoryForm.name === name ? 'bg-emerald-50 text-emerald-600 ring-2 ring-emerald-500' : 'text-gray-600'">
                  {{ emoji }}
                </button>
              </div>
            </div>
            <div class="flex gap-3 mt-6">
              <button @click="closeModal"
                      class="flex-1 px-3 py-1.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cancel
              </button>
              <button @click="handleSubmit"
                      class="flex-1 px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-500 hover:bg-emerald-600"
                      :disabled="isSubmitting || !categoryForm.name.trim()">
                {{ isSubmitting ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </BottomSheet>
    </template>
  </div>
</template>

<script>
import { ref, computed, onMounted, reactive } from 'vue'
import GlobalModal from '../../components/Global/GlobalModal.vue'
import BottomSheet from '../../components/BottomSheet.vue'
import { useWindowSize } from '@vueuse/core'
import { useCategoryStore } from '../../store/category'
import { useNotifications } from '../../composables/useNotifications'
import { emojiMixin } from '../../mixins/emojiMixin'

export default {
  name: 'CategoriesView',
  components: {
    GlobalModal,
    BottomSheet
  },
  setup() {
    const loading = ref(false)
    const isSubmitting = ref(false)
    const showAddModal = ref(false)
    const editingCategory = ref(null)
    const { width } = useWindowSize()
    const isMobile = computed(() => width.value < 768)
    const categoryStore = useCategoryStore()
    const { notify } = useNotifications()

    // Create a reactive reference to emojiMixin data
    const emojiData = reactive(emojiMixin.data())

    // Simplified emoji functions with error handling
    const getCategoryEmoji = (icon) => {
      console.log('getCategoryEmoji called with icon:', icon)
      try {
        const data = emojiMixin.data()
        const emoji = data.emojis.income[icon] || data.emojis.expense[icon] || '📌'
        console.log('emoji found:', emoji)
        return emoji
      } catch (e) {
        console.log('getCategoryEmoji error:', e)
        return '📌'
      }
    }

    // Initialize categoryForm with default values
    const categoryForm = ref({
      name: '',
      type: 'expense',
      color: '#10B981',
      icon: 'shopping'
    })

    // Make all emojis available and reactive
    const availableEmojis = computed(() => {
      const data = emojiMixin.data()
      return {
        ...data.emojis.income,
        ...data.emojis.expense
      }
    })

    // Handle icon selection
    const selectIcon = (iconName) => {
      console.log('Selecting icon:', iconName) // Debug log
      categoryForm.value.icon = iconName
    }

    // Reset form when modal is closed
    const closeModal = () => {
      showAddModal.value = false
      resetForm()
    }

    // Reset form with default values
    const resetForm = () => {
      categoryForm.value = {
        name: '',
        type: 'expense',
        color: '#10B981',
        icon: 'shopping'
      }
      editingCategory.value = null
    }

    // Pagination
    const currentPage = ref(1)
    const itemsPerPage = computed(() => categoryStore.pagination?.per_page || 10)
    const totalItems = computed(() => categoryStore.pagination?.total || 0)
    const totalPages = computed(() => categoryStore.pagination?.last_page || 1)
    const startIndex = computed(() => categoryStore.pagination?.from || 0)
    const endIndex = computed(() => categoryStore.pagination?.to || 0)

    // Computed properties for categories and pagination
    const categories = computed(() => categoryStore.categories || [])

    const displayedPages = computed(() => {
      if (!totalPages.value) return []
      
      const pages = []
      const maxVisiblePages = 5
      let start = Math.max(1, currentPage.value - Math.floor(maxVisiblePages / 2))
      let end = Math.min(totalPages.value, start + maxVisiblePages - 1)

      if (end - start + 1 < maxVisiblePages) {
        start = Math.max(1, end - maxVisiblePages + 1)
      }

      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      return pages
    })

    const getCategoryColor = (color) => {
      return { backgroundColor: color }
    }

    const editCategory = (category) => {
      editingCategory.value = category
      categoryForm.value = { ...category }
      showAddModal.value = true
    }

    const deleteCategory = async (category) => {
      if (!confirm('Are you sure you want to delete this category?')) return

      try {
        await categoryStore.deleteCategory(category.id)
        notify({
          title: 'Success',
          message: 'Category deleted successfully',
          type: 'success'
        })
      } catch (error) {
        notify({
          title: 'Error',
          message: 'Failed to delete category',
          type: 'error'
        })
      }
    }

    const handleSubmit = async () => {
      isSubmitting.value = true
      try {
        if (editingCategory.value) {
          await categoryStore.updateCategory(editingCategory.value.id, categoryForm.value)
          notify({
            title: 'Success',
            message: 'Category updated successfully',
            type: 'success'
          })
        } else {
          await categoryStore.addCategory(categoryForm.value)
          notify({
            title: 'Success',
            message: 'Category created successfully',
            type: 'success'
          })
        }
        showAddModal.value = false
        resetForm()
      } catch (error) {
        notify({
          title: 'Error',
          message: error.message || 'Failed to save category',
          type: 'error'
        })
      } finally {
        isSubmitting.value = false
        await categoryStore.fetchCategories(currentPage.value)
      }
    }

    const handlePageChange = async (page) => {
      currentPage.value = page
      loading.value = true
      try {
        await categoryStore.fetchCategories(page)
      } catch (error) {
        notify({
          title: 'Error',
          message: 'Failed to load categories',
          type: 'error'
        })
      } finally {
        loading.value = false
      }
    }

    onMounted(async () => {
      loading.value = true
      try {
        await categoryStore.fetchCategories(currentPage.value)
      } catch (error) {
        notify({
          title: 'Error',
          message: 'Failed to load categories',
          type: 'error'
        })
      } finally {
        loading.value = false
      }
    })

    return {
      loading,
      isSubmitting,
      showAddModal,
      editingCategory,
      categoryForm,
      categories,
      isMobile,
      currentPage,
      totalPages,
      startIndex,
      endIndex,
      paginatedCategories: categories,
      displayedPages,
      getCategoryColor,
      editCategory,
      deleteCategory,
      handleSubmit,
      handlePageChange,
      totalItems,
      itemsPerPage,
      getCategoryEmoji,
      selectIcon,
      closeModal,
      availableEmojis
    }
  }
}
</script>

<style scoped>
/* Add styles for emoji container */
.text-lg {
  line-height: 1;
  display: inline-block;
  transform: translateY(1px);
}

/* Add transition for smoother color changes */
.h-7 {
  transition: background-color 0.2s ease;
}

/* Add styles for selected icon */
.ring-2 {
  transition: all 0.2s ease;
}
</style>
