//TODO: Only enable admin routes available after on check of admin _ROLE

import { createRouter, createWebHistory } from "vue-router";
import { requireAuth, requireGuest } from "./auth-guard";
import { useTransactionStore } from "../store/transaction";
import { usePostHog } from "../composables/usePosthog.js";

const routes = [
  {
    path: "/",
    component: () => import("../views/Layouts/GuestLayoutView.vue"),
    beforeEnter: requireGuest,
    children: [
      {
        path: "login",
        component: () => import("../views/LoginView.vue"),
        name: 'login',
        meta: { requiresAuth: false, requiresSubscription: false }
      }
    ]
  },
  {
    path: "/",
    component: () => import("../views/Layouts/AuthLayoutView.vue"),
    beforeEnter: requireAuth,
    children: [
      {
        path: "overview",
        component: () => import("../views/HomeView.vue"),
        name: 'overview',
        meta: { requiresAuth: true, requiresSubscription: false }
      },
      {
        path: "wallets",
        component: () => import("../views/WalletsView.vue"),
        name: 'wallets',
        meta: { requiresAuth: true, requiresSubscription: false }
      },
      {
        path: "plans",
        component: () => import("../views/Settings/PricingPlansView.vue"),
        name: 'plans',
        meta: { requiresAuth: true, requiresSubscription: false }
      },
      {
        path: "settings",
        component: () => import("../views/Settings.vue"),
        name: 'settings',
        meta: { requiresAuth: true, requiresSubscription: false },
        children: [
          {
            path: "",
            component: () => import("../views/Settings.vue"),
            meta: { requiresAuth: true }
          },
          {
            path: "account",
            component: () => import("../views/Settings/AccountSettings.vue"),
            name: 'account-settings',
            meta: { requiresAuth: true }
          }
        ]
      },
      {
        path: "categories",
        component: () => import("../views/Settings/CategoriesView.vue"),
        name: 'categories',
        meta: { requiresAuth: true, requiresSubscription: false }
      },
      {
        path: "stats",
        component: () => import("../views/StatsView.vue"),
        name: 'stats',
        meta: { requiresAuth: true, requiresSubscription: false }
      },
      {
        path: "chat",
        component: () => import("../views/ChatView.vue"),
        name: 'chat',
        meta: { requiresAuth: true, requiresSubscription: false }
      },
      {
        path: "admin-dashboard",
        component: () => import("../views/Admin/DashboardView.vue"),
        name: 'admin-dashboard',
        meta: {
          adminOnly: true,
          requiresAuth: true,
          requiresSubscription: false
        }
      },
      {
        path: "feedbacks",
        component: () => import("../views/Admin/FeedbackView.vue"),
        name: 'feedbacks',
        meta: {
          adminOnly: true,
          requiresAuth: true,
          requiresSubscription: false
        }
      }
    ]
  },
  {
    path: "/:pathMatch(.*)*",
    component: () => import("../views/NotFoundView.vue"),
    name: 'not-found'
  }
];

const { posthog } = usePostHog()

export const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.afterEach((to) => {
  posthog.capture('$pageview')
})

router.beforeEach(async (to, from, next) => {
  // const authStore = useAuthStore()
  // const polarStore = usePolarStore()

  // Check if route requires subscription
  // if (to.meta.requiresSubscription && authStore.isAuthenticated) {
  //   try {
  //     await polarStore.fetchSubscriptionStatus()
  //     if (!polarStore.hasActiveSubscription && to.name !== 'plans') {
  //       next({ name: 'plans' })
  //       return
  //     }
  //   } catch (error) {
  //     console.error('Failed to check subscription:', error)
  //     next({ name: 'plans' })
  //     return
  //   }
  // }

  if (to.matched.some(record => record.meta.requiresTransactions)) {
    const transactionStore = useTransactionStore()
    if (transactionStore.transactions.length === 0) {
      await transactionStore.fetchTransactions()
    }
  }
  
  next()
})

export default router
