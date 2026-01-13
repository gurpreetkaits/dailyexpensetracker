import { defineStore } from 'pinia'

export const useLoaderStore = defineStore('loader', {
  state: () => ({
    isLoading: true,
    loadingText: 'Loading...'
  }),
  actions: {
    showLoader(text = 'Loading...') {
      this.isLoading = true
      this.loadingText = text
    },
    hideLoader() {
      this.isLoading = false
    }
  }
})
