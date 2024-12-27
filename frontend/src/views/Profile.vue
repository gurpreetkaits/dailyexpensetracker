<!-- src/views/ProfileView.vue -->
<template>
     <!-- Loading State -->
     <div v-if="loading" class="flex justify-center py-4">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>
    <div v-else class="max-w-2xl mx-auto p-4 space-y-6">
        <!-- Profile Header -->

        <!-- Profile Details -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <h3 class="text-lg font-semibold">Profile Details</h3>
            </div>

            <div class="p-4 space-y-4">
                <!-- Name Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" v-model="profile.name" disabled
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <!-- Email Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" v-model="profile.email" disabled
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" />
                </div>
            </div>
        </div>

        <!-- Account Statistics -->
        <div class="bg-white rounded-lg shadow py-3">
            <div class="p-4 border-b">
                <h3 class="text-lg font-semibold">Account Statistics</h3>
            </div>

            <div class="grid grid-cols-1 gap-4 p-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Member Since</p>
                    <p class="font-semibold">{{ formatDate(profile.created_at) }}</p>
                </div>
                <div class="pt-[14px]">
                    <button @click="handleLogout"
                        class="w-full flex items-center justify-between px-4 py-3 text-sm text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <span>Log Out</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
        <!-- Save Button -->
        <!-- <div class="flex justify-start">
            <button @click="saveProfile"
                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center gap-2 disabled:opacity-50"
                :disabled="isSaving">
                <span v-if="isSaving"
                    class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                {{ isSaving ? 'Saving...' : 'Save Changes' }}
            </button>
        </div> -->
    </div>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { UserCircle, Pencil } from 'lucide-vue-next'
import { useTransactionStore } from '../store/transaction'
import { useAuthStore } from '../store/auth'
export default {
    name: 'ProfileView',
    components: {
        UserCircle,
        Pencil
    },
    data() {
        return {
            isSaving: false,
            profile: {
                name: '',
                email: '',
                created_at: new Date()
            }
        }
    },
    computed: {
        ...mapState(useTransactionStore, ['transactions']),
        ...mapState(useAuthStore, ['user']),
        transactionCount() {
            return this.transactions.length
        }
    },
    methods: {
        ...mapActions(useAuthStore, ['getAuth', 'clearAuth']),
        formatDate(date) {
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            })
        },
        // async saveProfile() {
        //     this.isSaving = true
        //     try {
        //         // Implement your save logic here
        //         await useAuthStore().updateProfile(this.profile)
        //         success('Profile updated successfully')
        //     } catch (error) {
        //         error('Failed to update profile')
        //         console.error(error)
        //     } finally {
        //         this.isSaving = false
        //     }
        // },
        handleLogout() {
            if (!confirm('Sure!! you want to logout?')) {
                return
            }
            try {
                this.clearAuth()
                this.$router.push('/login')
            } catch (error) {
                console.error('Logout failed:', error)
            }
        }
    },
    async created() {
        try {
            let user = localStorage.getItem('user')
            if(user){
                user = JSON.parse(user);
                this.profile = { ...user }
            }else{
                this.profile =  this.getAuth()
            }
        } catch (error) {
            console.error('Error loading profile:', error)
        }
    }
}
</script>