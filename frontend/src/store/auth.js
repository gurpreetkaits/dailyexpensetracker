import { defineStore } from "pinia";
import { verifyToken } from "../services/AuthService";
export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
    token: localStorage.getItem("token"),
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    getIncome: (state) => state.user.income
  },

  actions: {
    setAuth(token, user) {
      this.token = token;
      this.user = user;
      localStorage.setItem("token", token);
      localStorage.setItem("user", JSON.stringify(user));
    },
    async getAuth() {
      const user = await verifyToken();
      this.setAuth(this.token, user);
      return user
    },
    clearAuth() {
      this.token = null;
      this.user = null;
      localStorage.removeItem("token");
    },
  },
});
