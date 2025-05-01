<template>
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full p-6 bg-white rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6">Login</h2>
            <div v-if="errorMessage" class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                {{ errorMessage }}
            </div>
            <form @submit.prevent="handleLogin">
<!--                 on>-->
<!--                <div class="relative my-6">-->
<!--                    <div class="absolute inset-0 flex items-center">-->
<!--                        <div class="w-full border-t border-gray-300"></div>-->
<!--                    </div>-->
<!--                    <div class="relative flex justify-center text-sm">-->
<!--                        <span class="px-2 bg-white text-gray-500">Or continue with</span>-->
<!--                    </div>-->
<!--                </div>-->
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
                <!-- Apple Login Button -->
                <button @click="appleLogin" type="button"
                        class="w-full flex items-center mt-2 justify-center gap-2 border border-gray-300 p-2 rounded hover:bg-gray-50"
                        :disabled="loading">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                        <path d="M17.569 12.6254C17.597 15.652 20.2179 16.6592 20.247 16.672C20.2248 16.7419 19.8282 18.0987 18.8699 19.5043C18.0266 20.7124 17.1481 21.9124 15.7995 21.9392C14.4805 21.9661 14.0466 21.1766 12.5263 21.1766C11.0061 21.1766 10.5228 21.9124 9.27796 21.9661C7.97582 22.0199 6.97418 20.6641 6.12328 19.4639C4.37257 16.9884 3.0577 12.4031 4.85793 9.31684C5.75115 7.78859 7.38897 6.80686 9.17663 6.78006C10.4384 6.75327 11.6296 7.62507 12.4234 7.62507C13.2173 7.62507 14.6497 6.59239 16.1749 6.75327C16.9223 6.78006 18.8774 7.03857 20.1476 8.83455C20.0518 8.89921 17.5468 10.4543 17.569 12.6254ZM15.0744 5.09197C15.7995 4.21432 16.2973 2.98243 16.1651 1.76147C15.1121 1.80973 13.8345 2.47646 13.0853 3.35411C12.4164 4.13995 11.8252 5.39862 11.9869 6.59239C13.1634 6.6884 14.3493 5.96963 15.0744 5.09197Z" fill="black"/>
                    </svg>
                    Continue with Apple
                </button>
                <!-- <GoogleLogin :callback="callback">
                </GoogleLogin> -->
            </form>
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
        },
        async appleLogin() {
            try {
                // Initialize Apple SignIn
                const data = await window.AppleID.auth.signIn()

                // Get user info from your backend
                const userObj = await getAppleUserInfo(data.authorization.code)
                const response = await login(userObj)

                // Set authentication and redirect
                this.authStore.setAuth(response.token, response.user)
                this.router.push('/overview')
            } catch (error) {
                this.handleError(error)
            }
        },
    }
}
</script>
