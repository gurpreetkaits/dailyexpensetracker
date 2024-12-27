import { createRouter, createWebHistory } from "vue-router";
import { requireAuth, requireGuest } from "./auth-guard";
import { useTransactionStore } from "../store/transaction";
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
    path: "/goals",
    component: () => import("../views/GoalsView.vue"),
    beforeEnter: requireAuth,
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
    if (transactionStore.transactions.length === 0) {
      await transactionStore.fetchTransactions()
    }
    next()
  } else {
    next()
  }
})