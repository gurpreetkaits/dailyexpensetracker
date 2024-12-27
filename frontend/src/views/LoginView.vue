<template>
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full p-6 bg-white rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6">Login</h2>
            <div v-if="errorMessage" class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                {{ errorMessage }}
            </div>
            <form @submit.prevent="handleLogin">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Email</label>
                    <input type="email" v-model="email" class="w-full p-2 border rounded" required>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" v-model="password"
                            class="w-full p-2 border rounded" required>
                        <button type="button" class="absolute right-2 top-2.5 text-gray-500"
                            @click="showPassword = !showPassword">
                            <svg class="w-5 h-5" :class="showPassword ? 'hidden' : 'block'" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="w-5 h-5" :class="showPassword ? 'block' : 'hidden'" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600"
                    :disabled="loading">
                    {{ loading ? 'Loading...' : 'Login' }}
                </button>
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Or continue with</span>
                    </div>
                </div>
                <!-- Google Login Button -->
                <button @click="googleLogin" type="button"
                    class="w-full flex items-center justify-center gap-2 border border-gray-300 p-2 rounded hover:bg-gray-50"
                    :disabled="loading">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                            fill="#4285F4" />
                        <path
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                            fill="#34A853" />
                        <path
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                            fill="#FBBC05" />
                        <path
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                            fill="#EA4335" />
                    </svg>
                    Continue with Google
                </button>
                <!-- <GoogleLogin :callback="callback">
                </GoogleLogin> -->
            </form>

            <p class="mt-4 text-center text-red-400">
                For some reason it doesn't support register for now, Continue With Google :)
                <!-- <router-link to="/register" class="text-blue-500">Register</router-link> -->
            </p>
        </div>
    </div>
</template>

<script>
import { login, getGoogleUserInfo } from '../services/AuthService'
import { useAuthStore } from '../store/auth'
import { useRouter } from 'vue-router'
import { googleAuthCodeLogin } from 'vue3-google-login'

export default {
    setup() {
        const router = useRouter()
        const authStore = useAuthStore()
        return { router, authStore }
    },
    data() {
        return {
            email: '',
            password: '',
            loading: false,
            showPassword: false,
            errorMessage: ''
        }
    },
    methods: {
        async handleLogin() {
            this.loading = true
            this.errorMessage = ''  // Clear previous error message

            const newUser = { email: this.email, password: this.password }
            try {
                const data = await login(newUser)
                this.authStore.setAuth(data.token, data.user)
                await this.router.push('/overview')
            } catch (error) {
                let oneLinerError = error.response?.data.error
                if (oneLinerError) {
                    this.errorMessage = oneLinerError
                }
                let multiErrors = error.response?.data.errors
                if (multiErrors) {
                    console.log(typeof multiErrors)
                    multiErrors
                    Object.entries(multiErrors).forEach(([field, errorMessages]) => {
                        this.errorMessage = errorMessages[0]
                    });
                }
            } finally {
                this.loading = false
            }
        },
        async googleLogin() {
            try {
                const googleResponse = await googleAuthCodeLogin();
                let googleCode = googleResponse.code

                const userObj = await getGoogleUserInfo(googleCode)
                const response = await login(userObj)
                this.authStore.setAuth(response.token, response.user)
                this.router.push('/overview')
            } catch (error) {
                let oneLinerError = error.response?.data.error
                if (oneLinerError) {
                    this.errorMessage = oneLinerError
                }
                let multiErrors = error.response?.data.errors
                if (multiErrors) {
                    console.log(typeof multiErrors)
                    multiErrors
                    Object.entries(multiErrors).forEach(([field, errorMessages]) => {
                        this.errorMessage = errorMessages[0]
                    });
                }
            }
        }
    }
}
</script>