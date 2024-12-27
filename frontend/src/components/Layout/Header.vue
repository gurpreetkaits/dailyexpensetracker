<template>
  <div>
    <header class="fixed top-0 left-0 right-0 bg-white shadow-sm z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-center h-16">
          <!-- Logo and Title -->
          <div class="flex items-center">
            <a href="/" class="flex items-center">
              <img src="../../assets/images/dailyexpensetracker.png" alt="ExpenseTracker Logo"
                class="h-10 w-10 object-contain rounded-lg" />
              <h1 class="ml-3 font-bold text-xl text-gray-900">Daily Expense Tracker</h1>
            </a>
          </div>
        </div>
      </div>
    </header>

    <!-- Spacer to prevent content from hiding under fixed header -->
    <div class="h-16"></div>
  </div>
</template>

<script>
export default {
  name: 'Header',
}
</script>