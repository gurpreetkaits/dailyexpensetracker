<!-- src/views/RegisterView.vue -->
<template>
  <div class="min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full p-6 bg-white rounded-lg shadow-md">
      <h2 class="text-2xl font-bold mb-6">Register</h2>

      <form @submit.prevent="handleRegister">
        <div class="mb-4">
          <label class="block text-gray-700 mb-2">Name</label>
          <input type="text" v-model="name" class="w-full p-2 border rounded" required>
        </div>

        <div class="mb-4">
          <label class="block text-gray-700 mb-2">Email</label>
          <input type="email" v-model="email" class="w-full p-2 border rounded" required>
        </div>

        <div class="mb-6">
          <label class="block text-gray-700 mb-2">Password</label>
          <div class="relative">
            <input :type="showPassword ? 'text' : 'password'" v-model="password" class="w-full p-2 border rounded"
              required>
            <button type="button" class="absolute right-2 top-2.5 text-gray-500" @click="showPassword = !showPassword">
              <svg class="w-5 h-5" :class="showPassword ? 'hidden' : 'block'" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg class="w-5 h-5" :class="showPassword ? 'block' : 'hidden'" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600" :disabled="loading">
          {{ loading ? 'Loading...' : 'Register' }}
        </button>
      </form>

      <p class="mt-4 text-center">
        Already have an account?
        <router-link to="/login" class="text-blue-500">Login</router-link>
      </p>
    </div>
  </div>
</template>

<script>
import { register } from '../services/AuthService'

export default {
  data() {
    return {
      name: '',
      email: '',
      password: '',
      loading: false,
      showPassword:false,
    }
  },
  methods: {
    async handleRegister() {
      this.loading = true
      try {
        const data = await register({
          name: this.name,
          email: this.email,
          password: this.password
        })
        localStorage.setItem('token', data.token)
        this.$router.push('/overview')
      } catch (error) {
        console.error(error)
        // Add error handling
      } finally {
        this.loading = false
      }
    }
  }
}
</script>