
//TODO: Only enable admin routes available after on check of admin _ROLE

import { createRouter, createWebHistory } from "vue-router";
import {requireAuth, requireGuest} from "./auth-guard";
import { useTransactionStore } from "../store/transaction";
import {useAuthStore} from "../store/auth.js";
const routes = [
  {
    path: "/",
    component: () => import("../views/LoginView.vue"),
    beforeEnter: requireGuest,
  },
  {
    path: "/login",
    component: () => import("../views/LoginView.vue"),
    beforeEnter: requireGuest,
  },
  {
    path: "/register",
    // component: () => import("../views/RegisterView.vue"),
    component: () => import("../views/LoginView.vue"),
    beforeEnter: requireGuest,
  },
  {
    path: "/overview",
    component: () => import("../views/HomeView.vue"),
    beforeEnter: requireAuth,
  },
  {
    path: "/profile",
    component: () => import("../views/Profile.vue"),
    beforeEnter: requireAuth,
  },
  {
    path: "/settings",
    component: () => import("../views/Settings.vue"),
    beforeEnter: requireAuth,
  },
  {
    path: "/stats",
    component: () => import("../views/StatsView.vue"),
    beforeEnter: requireAuth,
  },
  {
    path: "/chat",
    component: () => import("../views/ChatView.vue"),
    beforeEnter: requireAuth,
  },
  {
    path: "/goals",
    component: () => import("../views/GoalsView.vue"),
    beforeEnter: requireAuth,
  },
    {
        path: "/dashboard",
        component: () => import("../views/Admin/DashboardView.vue"),
        beforeEnter: requireAuth,
        meta: {
            adminOnly: true
        }
    },
    {
        path: "/feedbacks",
        component: () => import("../views/Admin/FeedbackView.vue"),
        beforeEnter: requireAuth,
        meta: {
            adminOnly: true
        }
    },
  {
    path: "/:pathMatch(.*)*",
    component: () => import("../views/NotFoundView.vue"),
  },
];

export const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  if (to.matched.some(record => record.meta.requiresTransactions)) {
    const transactionStore = useTransactionStore()
      const authStore = useAuthStore()
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
