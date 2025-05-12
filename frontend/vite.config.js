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
            "src": "src/assets/images/icons/det-48x48.png",
            "sizes": "48x48",
            "type": "image/png"
          },
          {
            "src": "src/assets/images/icons/det-72x72.png",
            "sizes": "72x72",
            "type": "image/png"
          },
          {
            "src": "src/assets/images/icons/det-96x96.png",
            "sizes": "96x96",
            "type": "image/png"
          },
          {
            "src": "src/assets/images/icons/det-128x128.png",
            "sizes": "128x128",
            "type": "image/png"
          },
          {
            "src": "src/assets/images/icons/det-144x144.png",
            "sizes": "144x144",
            "type": "image/png"
          },
          {
            "src": "src/assets/images/icons/det-152x152.png",
            "sizes": "152x152",
            "type": "image/png"
          },
          {
            "src": "src/assets/images/icons/det-192x192.png",
            "sizes": "192x192",
            "type": "image/png"
          },
          {
            "src": "src/assets/images/icons/det-256x256.png",
            "sizes": "256x256",
            "type": "image/png"
          },
          {
            "src": "src/assets/images/icons/det-384x384.png",
            "sizes": "384x384",
            "type": "image/png"
          },
          {
            "src": "src/assets/images/icons/det-512x512.png",
            "sizes": "512x512",
            "type": "image/png"
          }
        ],
        start_url: '/',
        display: 'standalone',
        background_color: '#ffffff'
      }
    })
  ]
})