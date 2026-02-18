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
      },
      {
        path: "/",
        component: () => import("../views/LoginView.vue"),
        name: 'home',
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
        path: "overview2",
        component: () => import("../views/DesktopDashboardView.vue"),
        name: 'overview2',
        meta: { requiresAuth: true, requiresSubscription: false }
      },
      {
        path: "wallets",
        component: () => import("../views/WalletsView.vue"),
        name: 'wallets',
        meta: { requiresAuth: true, requiresSubscription: false }
      },
      {
        path: "transfers",
        component: () => import("../views/TransfersView.vue"),
        name: 'transfers',
        meta: { requiresAuth: true, requiresSubscription: false }
      },
      {
        path: "bank-sync",
        component: () => import("../views/BankSyncView.vue"),
        name: 'bank-sync',
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
        meta: { requiresAuth: true, requiresSubscription: false }
      },
      {
        path: "settings/general",
        component: () => import("../views/Settings/GeneralSettingsView.vue"),
        name: 'general-settings',
        meta: { requiresAuth: true, requiresSubscription: false }
      },
      {
        path: "settings/profile",
        component: () => import("../views/Settings/ProfileSettingsView.vue"),
        name: 'profile-settings',
        meta: { requiresAuth: true, requiresSubscription: false }
      },
      {
        path: "settings/subscription",
        component: () => import("../views/Settings/SubscriptionView.vue"),
        name: 'subscription',
        meta: { requiresAuth: true, requiresSubscription: false }
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
        path: "feedback",
        component: () => import("../views/FeedbackView.vue"),
        name: 'feedback',
        meta: { requiresAuth: true, requiresSubscription: false }
      },
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
