<template>
    <div class="space-y-4 relative pb-24 m-3">
        <div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-semibold">Categories</h2>
                <div class="flex justify-start">
                    <button @click="openModal"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center gap-2 disabled:opacity-50">
                        <PlusIcon class="h-5 w-5" />
                        Add Category
                    </button>
                </div>
            </div>

            <ul class="divide-y">
                <li
                    v-for="cat in categoriesList"
                    :key="cat.id"
                    class="flex items-center justify-between py-2 hover:bg-gray-50 transition"
                >
                    <div class="flex items-center">
                        <component
                            :is="IconMap[cat.icon]"
                            v-if="IconMap[cat.icon]"
                            class="h-5 w-5 text-gray-600 mr-3"
                        />
                        <span v-else class="h-5 w-5 text-gray-400 mr-3">?</span>
                        <span class="text-gray-800">{{ cat.name }}</span>
                    </div>
                    <button
                        @click="removeCategory(cat.id)"
                        class="text-red-500 hover:text-red-700"
                    >
                        <Trash2Icon class="h-5 w-5" />
                    </button>
                </li>
                <li v-if="!categoriesList || categoriesList.length === 0" class="py-2 text-gray-500 text-center">
                    No categories yet. Add one!
                </li>
            </ul>
        </div>

        <BaseModal :show="showModal" @close="closeModal" title="Add Custom Category">
            <form @submit.prevent="addCategory">
                <div class="mb-4">
                    <label for="categoryName" class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                    <input
                        type="text"
                        id="categoryName"
                        v-model="newCategory.name"
                        required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., Groceries"
                    />
                </div>

                <div class="mb-4">
                    <label for="iconSearch" class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                    <div class="relative z-50">
                        <div class="flex items-center border border-gray-300 rounded-md focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500">
                             <span class="pl-3 pr-2 text-gray-400">
                                <component v-if="newCategory.icon && IconMap[newCategory.icon]" :is="IconMap[newCategory.icon]" class="h-5 w-5" />
                                <SearchIcon v-else class="h-5 w-5" />
                            </span>
                            <input
                                type="text"
                                id="iconSearch"
                                v-model="iconSearchTerm"
                                @focus="showIconDropdown = true"
                                @blur="handleIconSearchBlur"
                                class="w-full px-3 py-2 border-0 focus:outline-none focus:ring-0 rounded-md"
                                placeholder="Search icons..."
                            />
                        </div>
                        <Transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95"
                        >
                            <div v-if="showIconDropdown && filteredIcons.length > 0"
                                 class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-80 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                <button
                                    v-for="iconName in filteredIcons"
                                    :key="iconName"
                                    @click.prevent="selectIcon(iconName)"
                                    type="button"
                                    :class="[
                                        'w-full text-left px-4 py-2 hover:bg-blue-500 hover:text-white transition-colors flex items-center gap-3',
                                        newCategory.icon === iconName ? 'bg-blue-100 text-blue-700' : 'text-gray-900'
                                    ]"
                                >
                                    <component :is="IconMap[iconName]" class="h-5 w-5" />
                                    <span>{{ iconName }}</span>
                                </button>
                            </div>
                            <div v-else-if="showIconDropdown && iconSearchTerm && filteredIcons.length === 0"
                                 class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md p-3 text-sm text-gray-500">
                                No icons found for "{{ iconSearchTerm }}".
                            </div>
                        </Transition>
                    </div>
                </div>
            </form>

            <template #footer>
                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        @click="addCategory" :disabled="!newCategory.name || !newCategory.icon"
                        class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Save Category
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>

<script>
import { ref, reactive, computed, watch } from 'vue';
import BaseModal from '../components/Global/GlobalModal.vue'; // Adjust path if BaseModal.vue is in a different directory
import * as Icons from 'lucide-vue-next';
import { useSettingsStore } from '../store/settings'; // Adjust path to your Pinia store

// Destructure specific icons for direct use and gather the rest into AllIcons
const { PlusIcon, Trash2Icon, SearchIcon, ...AllIcons } = Icons;

export default {
    name: 'CategoryList',
    components: {
        BaseModal,
        PlusIcon,     // Icon for Add Category button
        Trash2Icon,   // Icon for Remove Category button
        SearchIcon,   // Icon for search input
        // Other icons from AllIcons are rendered dynamically using <component :is="">
    },
    setup() {
        const settingsStore = useSettingsStore(); // Access Pinia store

        // Reactive state for modal visibility and new category data
        const showModal = ref(false);
        const newCategory = reactive({
            name: '',
            parentId: '', // Empty string for no parent, or an ID for parent category
            icon: '',
        });
        const iconSearchTerm = ref(''); // Search term for icon picker
        const showIconDropdown = ref(false); // Controls visibility of icon dropdown

        // --- Computed Properties ---
        // Get categories from Pinia store
        const categoriesList = computed(() => settingsStore.categories);
        // Map of all available Lucide icons
        const IconMap = computed(() => AllIcons);

        // Sorted list of available icon names
        const availableIcons = computed(() => {
            return Object.keys(IconMap.value).sort();
        });

        // Filtered list of icons based on search term
        const filteredIcons = computed(() => {
            if (!iconSearchTerm.value) {
                // Show a limited subset of icons if search term is empty for performance
                return availableIcons.value.slice(0, 50);
            }
            const searchTermLower = iconSearchTerm.value.toLowerCase();
            return availableIcons.value.filter(iconName =>
                iconName.toLowerCase().includes(searchTermLower)
            );
        });

        // --- Methods ---
        // Resets the newCategory form fields
        const resetNewCategory = () => {
            newCategory.name = '';
            newCategory.parentId = '';
            // Set a default icon if available, otherwise empty
            newCategory.icon = availableIcons.value.length > 0 ? availableIcons.value[0] : '';
            iconSearchTerm.value = '';
        };

        // Opens the modal and resets the form
        const openModal = () => {
            resetNewCategory();
            showModal.value = true;
        };

        // Closes the modal
        const closeModal = () => {
            showModal.value = false;
            // Optionally, reset form on cancel, or only on successful save
            // resetNewCategory();
        };

        // Sets the selected icon and closes the icon dropdown
        const selectIcon = (iconName) => {
            newCategory.icon = iconName;
            iconSearchTerm.value = iconName; // Optionally, fill search input with selected icon name
            showIconDropdown.value = false;
        };

        // Handles adding a new category
        const addCategory = () => {
            // Basic validation
            if (!newCategory.name.trim() || !newCategory.icon) {
                // Replace alert with a more user-friendly notification system if available
                alert('Category name and icon are required.');
                return;
            }
            // Prepare category data to be added
            const categoryToAdd = {
                id: Date.now().toString(), // Use a more robust ID generation strategy in a real app
                name: newCategory.name.trim(),
                parentId: newCategory.parentId || null, // Store null if no parent is selected
                icon: newCategory.icon,
                // Potentially add other relevant category properties here
            };

            console.log('Adding category:', categoryToAdd);
            // TODO: Implement actual store dispatch to add the category
            // Example: settingsStore.addCategoryAction(categoryToAdd);
            alert(`Category "${categoryToAdd.name}" would be added. (Implement actual store logic)`);

            closeModal(); // Close modal after attempting to add
        };

        // Handles removing a category
        const removeCategory = (id) => {
            console.log('Removing category with id:', id);
            // TODO: Implement actual store dispatch to remove the category
            // Example: settingsStore.removeCategoryAction(id);
            alert(`Category with ID "${id}" would be removed. (Implement actual store logic)`);
        };

        // Handles blur event on icon search input to close dropdown
        const handleIconSearchBlur = (event) => {
            // Delay hiding to allow click on dropdown items to register
            setTimeout(() => {
                // Check if focus moved to an element within the dropdown itself
                if (!event.relatedTarget || !event.relatedTarget.closest('.absolute.z-10')) {
                    showIconDropdown.value = false;
                }
            }, 150); // Small delay (150ms)
        };

        // Watcher to potentially set a default icon if the categories list changes
        // (e.g., if it was initially empty and then populated)
        watch(categoriesList, (newList) => {
            // This logic might be redundant if resetNewCategory handles default icon selection adequately
            if (!newCategory.icon && newList.length > 0 && newList[0].icon && IconMap.value[newList[0].icon]) {
                // newCategory.icon = newList[0].icon; // Example: set to first category's icon
            }
        }, { immediate: true }); // Run watcher immediately on component mount

        // Return reactive state and methods to be used in the template
        return {
            showModal,
            newCategory,
            iconSearchTerm,
            showIconDropdown,
            categoriesList,
            IconMap,
            filteredIcons,
            openModal,
            closeModal,
            selectIcon,
            addCategory,
            removeCategory,
            handleIconSearchBlur,
            // Expose specific icons for direct use in template if needed
            PlusIcon,
            Trash2Icon,
            SearchIcon
        };
    },
};
</script>

<style scoped>
/* Add any component-specific styles here if Tailwind CSS isn't sufficient. */
/* Ensure the icon dropdown is positioned correctly and scrolls if content overflows. */
.relative {
    position: relative; /* Needed for absolute positioning of the dropdown */
}
.absolute.z-10 { /* Ensures icon dropdown appears above other elements */
    z-index: 10;
}
</style>
