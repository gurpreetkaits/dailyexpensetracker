//TODO: Only enable admin routes available after on check of admin _ROLE

import { createRouter, createWebHistory } from "vue-router";
import {requireAuth, requireGuest} from "./auth-guard";
import { useTransactionStore } from "../store/transaction";
import {useAuthStore} from "../store/auth.js";
import { usePostHog } from "../composables/usePosthog.js";
import { usePolarStore } from "../store/polar.js";

const routes = [
  {
    path: "/",
    component: () => import("../views/LoginView.vue"),
    beforeEnter: requireGuest,
    meta: { requiresAuth: false, requiresSubscription: true }
  },
  {
    path: "/login",
    component: () => import("../views/LoginView.vue"),
    beforeEnter: requireGuest,
    meta: { requiresAuth: false, requiresSubscription: false }
  },
  {
    path: "/register",
    // component: () => import("../views/RegisterView.vue"),
    component: () => import("../views/LoginView.vue"),
    beforeEnter: requireGuest,
    meta: { requiresAuth: false, requiresSubscription: false }
  },
  {
    path: "/overview",
    component: () => import("../views/HomeView.vue"),
    beforeEnter: requireAuth,
    meta: { requiresAuth: true, requiresSubscription: true }
  },
  {
    path: "/wallets",
    component: () => import("../views/WalletsView.vue"),
    beforeEnter: requireAuth,
    meta: { requiresAuth: true, requiresSubscription: true }
  },
  {
    path: "/plans",
    component: () => import("../views/Settings/PricingPlansView.vue"),
    beforeEnter: requireAuth,
    meta: { requiresAuth: true, requiresSubscription: false }
  },
  {
    path: "/settings",
    component: () => import("../views/Settings.vue"),
    beforeEnter: requireAuth,
    meta: { requiresAuth: true, requiresSubscription: false },
    children: [
      {
        path: "",
        component: () => import("../views/Settings.vue"),
        beforeEnter: requireAuth
      },
      {
        path: "account",
        component: () => import("../views/Settings/AccountSettings.vue"),
        beforeEnter: requireAuth
      }
    ]
  },
  {
    path: "/categories",
    component: () => import("../views/Settings/CategoriesView.vue"),
    beforeEnter: requireAuth,
    meta: { requiresAuth: true, requiresSubscription: true }
  },
  {
    path: "/stats",
    component: () => import("../views/StatsView.vue"),
    beforeEnter: requireAuth,
    meta: { requiresAuth: true, requiresSubscription: true }
  },
  {
    path: "/chat",
    component: () => import("../views/ChatView.vue"),
    beforeEnter: requireAuth,
    meta: { requiresAuth: true, requiresSubscription: true }
  },
    {
        path: "/admin-dashboard",
        component: () => import("../views/Admin/DashboardView.vue"),
        beforeEnter: requireAuth,
        meta: {
            adminOnly: true,
            requiresAuth: true,
            requiresSubscription: true
        }
    },
    {
        path: "/feedbacks",
        component: () => import("../views/Admin/FeedbackView.vue"),
        beforeEnter: requireAuth,
        meta: {
            adminOnly: true,
            requiresAuth: true,
            requiresSubscription: true
        }
    },
  {
    path: "/:pathMatch(.*)*",
    component: () => import("../views/NotFoundView.vue"),
  },
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
  const authStore = useAuthStore()
  const polarStore = usePolarStore()

  // Check if route requires auth
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }

  // Check if route requires subscription
  if (to.meta.requiresSubscription) {
    try {
      await polarStore.fetchSubscriptionStatus()
      if (!polarStore.hasActiveSubscription) {
        next({ name: 'settings' })
        return
      }
    } catch (error) {
      console.error('Failed to check subscription:', error)
      next({ name: 'settings' })
      return
    }
  }

  if (to.matched.some(record => record.meta.requiresTransactions)) {
    const transactionStore = useTransactionStore()
    if (transactionStore.transactions.length === 0) {
      await transactionStore.fetchTransactions()
    }
    // if(to.meta.adminOnly){
    //     const { user } = await authStore.getAuth()
    //     console.log('adminOLhyu',user)
    // }

    next()
  } else {
    next()
  }
})

export default router
