// src/main.js
import { createApp } from 'vue'
import App from './App.vue'
import { router } from './router'
import './style.css' // for Tailwind
import { createPinia } from 'pinia'
import './registerServiceWorker'
import vue3GoogleLogin from 'vue3-google-login'

const pinia = createPinia()
const app = createApp(App)
app.use(router)
app.use(pinia)
app.use(vue3GoogleLogin, {
    clientId: import.meta.env.VITE_GOOGLE_CLIENT
  })
  
app.mount('#app')