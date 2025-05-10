<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar slot -->
        <!-- <aside class="fixed left-0 top-0 bottom-0 w-16 bg-white border-r flex flex-col items-center py-4 space-y-6"> -->
            <slot name="sidebar"/>
        <!-- </aside> -->

        <!-- Main content -->
        <div class="flex-1 sm:ml-16 flex flex-col">
            <!-- Header -->
            <header class="w-full">
                <slot name="header"/>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-auto p-6">
                <slot />
            </main>

            <!-- Mobile nav slot (optional) -->
            <footer>
                <slot name="mobile-nav"/>
            </footer>
        </div>
    </div>
</template>

<script setup>
// nothing needed here
</script>

<style scoped>
/* You can narrow the border color or tweak shadow for even more minimalism */
</style>
