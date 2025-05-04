// src/router/auth-guard.js
import { useAuthStore } from '../store/auth'

export const requireAuth = (to, from, next) => {
  const authStore = useAuthStore()

  if (!authStore.isAuthenticated) {
    next('/login')
  } else {
    next()
  }
}

export const requireGuest = (to, from, next) => {
  const authStore = useAuthStore()

  if (authStore.isAuthenticated) {
    next('/overview')
  } else {
    next()
  }
}
