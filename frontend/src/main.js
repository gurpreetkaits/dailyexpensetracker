// src/main.js
import { createApp } from 'vue'
import App from './App.vue'
import { router } from './router'
import './style.css' // for Tailwind
import { createPinia } from 'pinia'
import './registerServiceWorker'
import vue3GoogleLogin from 'vue3-google-login'
import posthog from 'posthog-js'

// posthog.init('phc_aHfozAqV6TlGwu7ysNNiNGQ45Cgi7YLETXDuUwBVsAX',
//     {
//         api_host: 'https://us.i.posthog.com',
//         person_profiles: 'identified_only'
//     }
// )
const pinia = createPinia()
const app = createApp(App)
app.use(router)
app.use(pinia)
app.use(vue3GoogleLogin, {
    clientId: import.meta.env.VITE_GOOGLE_CLIENT
  })
  
app.mount('#app')