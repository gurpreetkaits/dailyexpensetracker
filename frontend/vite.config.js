import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    vue(),
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: ['favicon.ico', 'apple-touch-icon.png', 'masked-icon.svg'],
      manifest: {
        name: 'Daily Expense Tracker',
        short_name: 'Daily Expenses',
        description: 'Track your daily expenses',
        theme_color: '#ffffff',
        icons: [
          {
            src: 'src/assets/images/android/192.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: 'src/assets/images/android/512.png',
            sizes: '512x512',
            type: 'image/png',        
            purpose: 'any maskable'
          }
        ],
        start_url: '/',
        display: 'standalone',
        background_color: '#ffffff'
      }
    })
  ]
})