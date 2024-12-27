<!-- src/views/NotFoundView.vue -->
<template>
    <div class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-800 mb-4">404</h1>
        <p class="text-2xl text-gray-600 mb-8">Page not found</p>
        <router-link 
          :to="redirectPath" 
          class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600"
        >
          Back to {{ isAuthenticated ? 'Dashboard' : 'Login' }}
        </router-link>
      </div>
    </div>
  </template>
  
  <script>
  import { useAuthStore } from '../store/auth'
  
  export default {
    name: 'NotFound',
    computed: {
      isAuthenticated() {
        const authStore = useAuthStore()
        return authStore.isAuthenticated
      },
      redirectPath() {
        return this.isAuthenticated ? '/overview' : '/login'
      }
    }
  }
  </script>