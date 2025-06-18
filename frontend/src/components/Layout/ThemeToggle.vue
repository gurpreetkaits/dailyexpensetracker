<template>
  <button 
    @click="toggleTheme" 
    class="flex items-center p-2 rounded-md transition-colors text-sm font-medium"
    :class="isDarkMode ? 'text-yellow-500 hover:text-yellow-600' : 'text-gray-700 hover:text-gray-900'"
    aria-label="Toggle dark mode"
  >
    <Sun v-if="isDarkMode" class="w-5 h-5 mr-3 flex-shrink-0" />
    <Moon v-else class="w-5 h-5 mr-3 flex-shrink-0" />
    <span>{{ isDarkMode ? 'Light Mode' : 'Dark Mode' }}</span>
  </button>
</template>

<script>
import { Sun, Moon } from 'lucide-vue-next';
import { mapState, mapActions } from 'pinia';
import { useThemeStore } from '../../store/theme';

export default {
  name: 'ThemeToggle',
  components: { Sun, Moon },
  computed: {
    ...mapState(useThemeStore, ['darkMode']),
    isDarkMode() {
      return this.darkMode;
    }
  },
  methods: {
    ...mapActions(useThemeStore, ['toggleDarkMode']),
    toggleTheme() {
      this.toggleDarkMode();
    }
  }
};
</script>
