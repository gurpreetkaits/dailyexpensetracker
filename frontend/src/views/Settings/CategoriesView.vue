<template>
  <div class=" mx-auto p-4 mb-10">
    <!-- Header with Add Button and Pagination -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
      <div class="flex justify-between items-center">
        <div class="flex items-center gap-4">
          <button @click="showAddModal = true"
                  class="bg-emerald-500 text-white px-3 py-1.5 rounded-lg hover:bg-emerald-600 transition-colors items-center gap-1.5 text-sm flex">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add
          </button>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex items-center gap-4">
          <div class="text-sm text-gray-500">
            Showing {{ startIndex }}-{{ endIndex }} of {{ totalItems }}
          </div>
          <div class="flex items-center gap-2">
            <button v-for="link in paginationLinks"
                    :key="link.label"
                    @click="handlePageChange(link.url)"
                    :disabled="!link.url"
                    class="px-3 py-1.5 rounded-lg border text-sm font-medium transition-colors disabled:opacity-50"
                    :class="[
                      link.active
                        ? 'bg-emerald-500 text-white border-emerald-500'
                        : 'text-gray-700 border-gray-300 hover:bg-gray-50',
                      !link.url ? 'text-gray-400 border-gray-200' : ''
                    ]"
                    v-html="link.label">
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Categories List -->
    <div class="bg-white rounded-xl shadow-sm p-4 overflow-x-auto min-h-[440px] relative">
      <!-- Loading State -->
      <div v-if="loading" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-10">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500"></div>
      </div>

      <template v-if="!loading">
        <table v-if="categories.length > 0" class="min-w-full text-sm">
          <thead>
            <tr class="text-gray-500 border-b">
              <th class="py-2 text-left font-medium">Category</th>
              <th class="py-2 text-left font-medium">Type</th>
              <th class="py-2 text-left font-medium">Transactions</th>
              <th class="py-2 text-left font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="category in paginatedCategories" :key="category.id" class="border-b last:border-0">
              <td class="py-1">
                <div class="flex items-center gap-2">
                  <div class="h-6 w-6 rounded-full flex items-center justify-center shrink-0"
                       :style="getCategoryColor(category.color)">
                    <component :is="category.icon || 'ShoppingBag'" class="h-3 w-3 text-white" />
                  </div>
                  <span class="font-medium text-gray-900 text-sm">{{ category.name }}</span>
                </div>
              </td>
              <td class="py-2">
                <span :class="category.type === 'expense' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                      class="px-2 py-0.5 rounded-full text-xs capitalize">
                  {{ category.type }}
                </span>
              </td>
              <td class="py-2 text-gray-600">
                {{ category.transaction_count || 0 }} transactions
              </td>
              <td class="py-2">
                <div class="flex items-center gap-1">
                  <button @click="editCategory(category)"
                          class="p-1 text-gray-400 hover:text-blue-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button @click="deleteCategory(category)"
                          class="p-1 text-gray-400 hover:text-red-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center justify-center min-h-[400px]">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
      </template>
    </div>

    <!-- Floating Action Button (Mobile) -->
    <button @click="showAddModal = true"
      class="md:hidden fixed bottom-[80px] left-1/2 transform -translate-x-1/2 inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-500 text-white shadow-xl hover:bg-emerald-600 hover:shadow-2xl transition-all">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
    </button>

    <!-- Add/Edit Category Modal -->
    <GlobalModal
      v-model="showAddModal"
      :title="editingCategory ? 'Edit Category' : 'Add Category'"
    >
      <form @submit.prevent="handleSubmit" class="space-y-5">
        <div>
          <input
            type="text"
            v-model="categoryForm.name"
            placeholder="Category name"
            class="w-full px-3 py-2 border-0 border-b border-gray-200 focus:ring-0 focus:border-emerald-500 text-sm"
            required
          />
        </div>
        <div class="flex gap-4">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="radio"
              v-model="categoryForm.type"
              value="expense"
              class="w-4 h-4 text-emerald-500 border-gray-300 focus:ring-emerald-500"
            />
            <span class="text-sm text-gray-700">Expense</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="radio"
              v-model="categoryForm.type"
              value="income"
              class="w-4 h-4 text-emerald-500 border-gray-300 focus:ring-emerald-500"
            />
            <span class="text-sm text-gray-700">Income</span>
          </label>
        </div>
        <div>
          <div class="text-sm text-gray-700 mb-2">Color</div>
          <div class="grid grid-cols-8 gap-2">
            <button v-for="color in colors"
                    :key="color"
                    type="button"
                    @click="categoryForm.color = color"
                    class="w-7 h-7 rounded-full border-2 transition-all"
                    :style="{ backgroundColor: color }"
                    :class="[
                      categoryForm.color === color ? 'border-emerald-500 scale-110' : 'border-transparent hover:border-gray-300'
                    ]">
            </button>
          </div>
        </div>
        <div>
          <div class="text-sm text-gray-700 mb-2">Icon</div>
          <div class="grid grid-cols-8 gap-2 max-h-40 overflow-y-auto p-1">
            <button v-for="icon in icons"
                    :key="icon.name"
                    type="button"
                    @click="categoryForm.icon = icon.name"
                    class="h-8 w-8 rounded-lg flex items-center justify-center hover:bg-gray-100 transition-colors"
                    :class="categoryForm.icon === icon.name ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600'">
              <component :is="icon.component" class="h-4 w-4" />
            </button>
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button"
                  @click="showAddModal = false"
                  class="px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">
            Cancel
          </button>
          <button type="submit"
                  class="px-3 py-1.5 text-sm font-medium text-white bg-emerald-500 rounded-md hover:bg-emerald-600"
                  :disabled="isSubmitting">
            {{ isSubmitting ? 'Saving...' : (editingCategory ? 'Save' : 'Add') }}
          </button>
        </div>
      </form>
    </GlobalModal>

    <!-- Delete Confirmation Modal -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div v-if="showDeleteDialog"
           class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm"
           @click="showDeleteDialog = false">
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="relative bg-white rounded-lg shadow-lg max-w-sm w-full mx-4 p-6"
               @click.stop>
            <h3 class="text-lg font-medium text-gray-900 mb-2">
              Delete Category
            </h3>
            <p class="text-sm text-gray-500 mb-6">
              Are you sure you want to delete "{{ categoryToDelete?.name }}"? This action cannot be undone.
            </p>
            <div class="flex justify-end gap-3">
              <button type="button"
                      @click="showDeleteDialog = false"
                      class="px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">
                Cancel
              </button>
              <button type="button"
                      @click="confirmDelete"
                      class="px-3 py-1.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script>
import { ref, computed, onMounted, Transition } from 'vue'
import GlobalModal from '../../components/Global/GlobalModal.vue'
import BottomSheet from '../../components/BottomSheet.vue'
import { useWindowSize } from '@vueuse/core'
import { useCategoryStore } from '../../store/category'
import { useNotifications } from '../../composables/useNotifications'
import {
  ShoppingBag, Home, Car, Plane, Utensils, Coffee,
  ShoppingCart, Gift, Heart, Book, GraduationCap, Briefcase,
  Dumbbell, Music, GamepadIcon, Phone, Wifi, Zap,
  Droplet, Pill, Stethoscope, Bus, Train, Ship,
  CircleDollarSign, PiggyBank, Wallet, CreditCard, BanknoteIcon
} from 'lucide-vue-next'

export default {
  name: 'CategoriesView',
  components: {
    GlobalModal,
    BottomSheet,
    Transition,
    ShoppingBag, Home, Car, Plane, Utensils, Coffee,
    ShoppingCart, Gift, Heart, Book, GraduationCap, Briefcase,
    Dumbbell, Music, GamepadIcon, Phone, Wifi, Zap,
    Droplet, Pill, Stethoscope, Bus, Train, Ship,
    CircleDollarSign, PiggyBank, Wallet, CreditCard, BanknoteIcon
  },
  setup() {
    const loading = ref(false)
    const isSubmitting = ref(false)
    const showAddModal = ref(false)
    const showDeleteDialog = ref(false)
    const categoryToDelete = ref(null)
    const editingCategory = ref(null)
    const { width } = useWindowSize()
    const isMobile = computed(() => width.value < 768)
    const categoryStore = useCategoryStore()
    const { notify } = useNotifications()

    const colors = [
      '#10B981', // emerald
      '#3B82F6', // blue
      '#8B5CF6', // violet
      '#EC4899', // pink
      '#F59E0B', // amber
      '#EF4444', // red
      '#6366F1', // indigo
      '#14B8A6', // teal
      '#F97316', // orange
      '#84CC16', // lime
      '#06B6D4', // cyan
      '#A855F7', // purple
      '#D946EF', // fuchsia
      '#22C55E', // green
      '#EAB308', // yellow
      '#78716C', // stone
    ]

    const categoryForm = ref({
      name: '',
      type: 'expense',
      color: colors[0], // Set default color to first color
      icon: 'ShoppingBag'
    })

    const icons = [
      { name: 'ShoppingBag', component: ShoppingBag },
      { name: 'Home', component: Home },
      { name: 'Car', component: Car },
      { name: 'Plane', component: Plane },
      { name: 'Utensils', component: Utensils },
      { name: 'Coffee', component: Coffee },
      { name: 'ShoppingCart', component: ShoppingCart },
      { name: 'Gift', component: Gift },
      { name: 'Heart', component: Heart },
      { name: 'Book', component: Book },
      { name: 'GraduationCap', component: GraduationCap },
      { name: 'Briefcase', component: Briefcase },
      { name: 'Dumbbell', component: Dumbbell },
      { name: 'Music', component: Music },
      { name: 'GamepadIcon', component: GamepadIcon },
      { name: 'Phone', component: Phone },
      { name: 'Wifi', component: Wifi },
      { name: 'Zap', component: Zap },
      { name: 'Droplet', component: Droplet },
      { name: 'Pill', component: Pill },
      { name: 'Stethoscope', component: Stethoscope },
      { name: 'Bus', component: Bus },
      { name: 'Train', component: Train },
      { name: 'Ship', component: Ship },
      { name: 'CircleDollarSign', component: CircleDollarSign },
      { name: 'PiggyBank', component: PiggyBank },
      { name: 'Wallet', component: Wallet },
      { name: 'CreditCard', component: CreditCard },
      { name: 'BanknoteIcon', component: BanknoteIcon }
    ]

    // Computed properties for categories and pagination
    const categories = computed(() => categoryStore.categories)

    const paginatedCategories = computed(() => {
      return categories.value
    })

    const pagination = computed(() => categoryStore.pagination)
    const totalPages = computed(() => pagination.value?.last_page || 1)
    const startIndex = computed(() => pagination.value?.from || 0)
    const endIndex = computed(() => pagination.value?.to || 0)
    const totalItems = computed(() => pagination.value?.total || 0)
    const paginationLinks = computed(() => pagination.value?.links || [])

    const getCategoryColor = (color) => {
      return { backgroundColor: color }
    }

    const editCategory = (category) => {
      editingCategory.value = category
      categoryForm.value = { ...category }
      showAddModal.value = true
    }

    const deleteCategory = (category) => {
      categoryToDelete.value = category
      showDeleteDialog.value = true
    }

    const confirmDelete = async () => {
      if (!categoryToDelete.value) return

      try {
        await categoryStore.deleteCategory(categoryToDelete.value.id)
        notify({
          title: 'Success',
          message: 'Category deleted successfully',
          type: 'success'
        })
        showDeleteDialog.value = false
        categoryToDelete.value = null
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
        await categoryStore.fetchCategories()
      }
    }

    const resetForm = () => {
      categoryForm.value = {
        name: '',
        type: 'expense',
        color: colors[0],
        icon: 'ShoppingBag'
      }
      editingCategory.value = null
    }

    const handlePageChange = async (url) => {
      if (!url) return
      const page = new URL(url).searchParams.get('page')
      if (!page) return

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
        await categoryStore.fetchCategories()
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
      showDeleteDialog,
      categoryToDelete,
      editingCategory,
      categoryForm,
      categories,
      icons,
      isMobile,
      paginatedCategories,
      pagination,
      totalPages,
      startIndex,
      endIndex,
      totalItems,
      paginationLinks,
      getCategoryColor,
      editCategory,
      deleteCategory,
      confirmDelete,
      handleSubmit,
      handlePageChange,
      colors
    }
  }
}
</script>
