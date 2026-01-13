<template>
  <Transition name="fade">
    <div v-if="isLoading" class="splash-loader">
      <div class="splash-content">
        <div class="logo-container">
          <img
            src="../../assets/images/icons/det-192x192.png"
            alt="Daily Expense Tracker"
            class="logo"
          />
        </div>
        <h1 class="app-title">Daily Expense Tracker</h1>
        <div class="loader-dots">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue'
import { useLoaderStore } from '../../store/loader'

const loaderStore = useLoaderStore()
const isLoading = computed(() => loaderStore.isLoading)
</script>

<style scoped>
.splash-loader {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.splash-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.logo-container {
  animation: pulse 2s ease-in-out infinite;
}

.logo {
  width: 80px;
  height: 80px;
  border-radius: 20px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.app-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
  letter-spacing: -0.5px;
}

.loader-dots {
  display: flex;
  gap: 6px;
}

.loader-dots span {
  width: 8px;
  height: 8px;
  background-color: #10b981;
  border-radius: 50%;
  animation: bounce 1.4s ease-in-out infinite both;
}

.loader-dots span:nth-child(1) {
  animation-delay: -0.32s;
}

.loader-dots span:nth-child(2) {
  animation-delay: -0.16s;
}

.loader-dots span:nth-child(3) {
  animation-delay: 0s;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

@keyframes bounce {
  0%, 80%, 100% {
    transform: scale(0);
  }
  40% {
    transform: scale(1);
  }
}

/* Fade transition */
.fade-enter-active {
  transition: opacity 0.3s ease;
}

.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
