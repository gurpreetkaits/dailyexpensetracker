<template>
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <!-- Login Card -->
        <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-lg mx-auto">
            <div>
                <!-- Placeholder for Logo -->
                <div class="flex justify-center mb-6">
                    <!-- Replace this div with your logo component or img tag -->
                    <!-- Example: <img class="mx-auto h-12 w-auto" src="/path/to/your/logo.svg" alt="Your Company"> -->
                    <div class=" h-16 w-16 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                        <img src="../assets/images/dailyexpensetracker.png" alt="Logo" class="shadow-sm w-16 h-16 mx-auto mb-4 rounded-full object-cover">
                    </div>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Sign in to your account
                </h2>
            </div>
            <div v-if="errorMessage" class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                {{ errorMessage }}
            </div>
            <form @submit.prevent="handleLogin" v-if="!showSurvey && !isLoggedIn" class="mt-8 space-y-6">
                <!-- Google Login Button -->
                <div v-if="isDev">
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
                </div>
                <button v-if="!isLoggedIn" @click="googleLogin" type="button"
                    class="w-full flex items-center justify-center gap-2 border border-gray-300 p-3 rounded-md hover:bg-gray-50 transition-colors duration-150"
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
                <router-link v-else to="/overview2" class="w-full bg-emerald-600 text-white p-3 rounded-lg text-center hover:bg-emerald-700 transition-colors duration-150">
                  Go to Dashboard
                </router-link>
                <!-- Apple Login Button -->
                <!-- <GoogleLogin :callback="callback">
                </GoogleLogin> -->
            </form>

            <!-- Dashboard shortcut when already logged in -->
            <div v-else-if="isLoggedIn" class="mt-8 space-y-6 w-full max-w-md">
              <router-link to="/overview2" class="block w-full bg-emerald-600 text-white p-3 rounded-lg text-center hover:bg-emerald-700 transition-colors font-medium">
                Go to Dashboard
              </router-link>
            </div>
        </div>
        <!-- Survey Modal -->
        <div v-if="showSurvey" class="fixed inset-0 bg-gray-800 bg-opacity-75 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-auto shadow-xl transform transition-all">
                <!-- Step 1: What are you looking for? -->
                <div v-if="surveyStep === 1">
                    <h3 class="text-xl font-semibold mb-2 text-gray-800">Welcome to Daily Expense Tracker!</h3>
                    <p class="text-sm text-gray-600 mb-4">To help us improve, what are you primarily looking to achieve?</p>
                    <div class="space-y-2 mb-4">
                        <label v-for="option in userNeedsOptions" :key="option" class="flex items-center p-3 border rounded-lg hover:bg-gray-100 cursor-pointer transition-colors">
                            <input type="checkbox" :value="option" v-model="selectedNeeds" class="form-checkbox h-5 w-5 text-emerald-600 rounded focus:ring-emerald-500">
                            <span class="ml-3 text-gray-700">{{ option }}</span>
                        </label>
                        <input type="text" v-model="otherNeed" placeholder="Something else? Let us know..." class="w-full p-2 border rounded-lg mt-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <button @click="nextSurveyStep" class="w-full bg-emerald-600 text-white p-3 rounded-lg hover:bg-emerald-700 transition-colors font-medium">
                        Next
                    </button>
                </div>

                <!-- Step 2: Happiness and Willingness to Pay -->
                <div v-if="surveyStep === 2">
                    <h3 class="text-xl font-semibold mb-4 text-gray-800">Quick Feedback</h3>
                    <div class="mb-4">
                        <p class="text-gray-700 mb-2">Are you finding the app helpful so far for your needs?</p>
                        <div class="flex gap-3">
                            <button @click="isHappy = true" :class="isHappy === true ? 'bg-green-500 text-white' : 'bg-gray-200 hover:bg-gray-300'" class="flex-1 p-3 rounded-lg transition-colors">Yes</button>
                            <button @click="isHappy = false" :class="isHappy === false ? 'bg-red-500 text-white' : 'bg-gray-200 hover:bg-gray-300'" class="flex-1 p-3 rounded-lg transition-colors">No</button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <p class="text-gray-700 mb-2">
                            If we perfectly address your needs
                            <span v-if="selectedNeeds.length > 0 || otherNeed.trim()">
                                (e.g., {{ formattedNeedsQuery }})
                            </span>,
                            would you consider a paid plan for these advanced features?
                        </p>
                        <div class="flex gap-3">
                            <button @click="willingToPay = true" :class="willingToPay === true ? 'bg-green-500 text-white' : 'bg-gray-200 hover:bg-gray-300'" class="flex-1 p-3 rounded-lg transition-colors">Yes, definitely!</button>
                            <button @click="willingToPay = false" :class="willingToPay === false ? 'bg-red-500 text-white' : 'bg-gray-200 hover:bg-gray-300'" class="flex-1 p-3 rounded-lg transition-colors">Not sure / No</button>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="feedbackText">Any other comments? (Optional)</label>
                        <textarea v-model="feedbackText" id="feedbackText" rows="3" class="w-full p-2 border rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>
                    <button @click="submitSurvey" class="w-full bg-emerald-600 text-white p-3 rounded-lg hover:bg-emerald-700 transition-colors font-medium">
                        Submit & Continue
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { login, getGoogleUserInfo, submitSurvey as submitSurveyAPI } from '../services/AuthService'
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
            errorMessage: '',
            isDev: process.env.NODE_ENV === 'development',

            // Survey state
            showSurvey: false,
            surveyStep: 1,
            userNeedsOptions: [
                'Detailed expense tracking',
                'Easy budgeting tools',
                'Investment portfolio tracking',
                'AI-powered financial insights (Chat)',
                'Customizable financial reports',
                'Tracking multiple bank accounts/wallets'
            ],
            selectedNeeds: [],
            otherNeed: '',
            isHappy: null,
            willingToPay: null,
            feedbackText: ''
        }
    },
    computed: {
        formattedNeedsQuery() {
            const allNeeds = [...this.selectedNeeds];
            if (this.otherNeed.trim()) {
                allNeeds.push(this.otherNeed.trim());
            }
            if (allNeeds.length === 0) return "your primary financial goals";
            if (allNeeds.length === 1) return allNeeds[0];
            if (allNeeds.length === 2) return allNeeds.join(' and ');
            return allNeeds.slice(0, -1).join(', ') + ', and ' + allNeeds.slice(-1);
        },
        isLoggedIn() {
            return !!this.authStore.token;
        }
    },
    methods: {
        async proceedToApp() {
            try {
                await this.router.push({ name: 'overview2' });
            } catch (error) {
                console.error('Navigation error:', error);
                // Fallback navigation if the first attempt fails
                this.router.replace({ path: '/overview2' });
            }
        },

        showPostLoginSurvey() {
            this.showSurvey = false;
            this.surveyStep = 1;
            this.selectedNeeds = [];
            this.otherNeed = '';
            this.isHappy = null;
            this.willingToPay = null;
            this.feedbackText = '';
        },

        nextSurveyStep() {
            if (this.surveyStep === 1) {
                if (this.selectedNeeds.length === 0 && !this.otherNeed.trim()) {
                    this.errorMessage = "Please select at least one option or tell us what you're looking for.";
                    setTimeout(() => this.errorMessage = '', 3000);
                    return;
                }
                this.errorMessage = '';
                this.surveyStep = 2;
            }
        },

        async submitSurvey() {
            if (this.isHappy === null || this.willingToPay === null) {
                this.errorMessage = "Please answer all questions in this step.";
                setTimeout(() => this.errorMessage = '', 3000);
                return;
            }
            this.errorMessage = '';

            const surveyData = {
                userId: this.authStore.user?.id,
                userEmail: this.authStore.user?.email,
                selectedNeeds: this.selectedNeeds,
                otherNeed: this.otherNeed.trim(),
                isHappy: this.isHappy,
                willingToPay: this.willingToPay,
                feedbackText: this.feedbackText.trim(),
                timestamp: new Date().toISOString()
            };

            // if (window.posthog) {
            //      window.posthog.capture('post_login_survey_completed', surveyData);
            // }

            try {
                await submitSurveyAPI(surveyData);
                localStorage.setItem('surveyCompleted', 'true');
                this.showSurvey = false;
                await this.proceedToApp();
            } catch (e) {
                this.errorMessage = 'Failed to save survey. Please try again.';
            }
        },

        async successfulLoginActions() {
            // Only show survey if not completed before
            if (!localStorage.getItem('surveyCompleted')) {
                this.showPostLoginSurvey();
            } else {
                await this.proceedToApp();
            }
        },

        handleError(error) {
            let oneLinerError = error.response?.data.error;
            if (oneLinerError) {
                this.errorMessage = oneLinerError;
            } else {
                let multiErrors = error.response?.data.errors;
                if (multiErrors) {
                    this.errorMessage = Object.values(multiErrors).flat().join(' ');
                } else if (error.message) {
                    this.errorMessage = error.message;
                } else {
                    this.errorMessage = 'An unexpected error occurred. Please try again.';
                }
            }
            console.error("Login Error:", error);
        },

        async handleLogin() {
            this.loading = true;
            this.errorMessage = '';
            const newUser = { email: this.email, password: this.password };
            try {
                const data = await login(newUser);
                this.authStore.setAuth(data.token, data.user);
                await this.successfulLoginActions();
            } catch (error) {
                this.handleError(error);
            } finally {
                this.loading = false;
            }
        },

        async googleLogin() {
            this.loading = true;
            this.errorMessage = '';
            try {
                const googleResponse = await googleAuthCodeLogin();
                let googleCode = googleResponse.code;
                const userObj = await getGoogleUserInfo(googleCode);
                const response = await login(userObj);
                this.authStore.setAuth(response.token, response.user);
                await this.successfulLoginActions();
            } catch (error) {
                this.handleError(error);
            } finally {
                this.loading = false;
            }
        },

        async appleLogin() {
            try {
                const data = await window.AppleID.auth.signIn()
                const userObj = await getAppleUserInfo(data.authorization.code)
                const response = await login(userObj)
                this.authStore.setAuth(response.token, response.user)
                this.router.push('/overview2')
            } catch (error) {
                this.handleError(error)
            }
        },
    },
    mounted() {
        // If already logged in, redirect to overview
        if (this.authStore.token) {
            this.router.push('/overview2');
        }
    }
}
</script>
