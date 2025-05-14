import axiosConf from "../config/axiosConf.js";

export const createSubscription = async () => {
  const { data } = await axiosConf.post("/api/subscription/checkout");
  return data;
};

export const getSubscriptionStatus = async () => {
  const { data } = await axiosConf.get("/api/subscription/status");
  return data;
};

export const verifyCheckoutSession = async (checkoutId) => {
  const { data } = await axiosConf.post("/api/subscription/verify-session", {
    checkout_id: checkoutId
  });
  return data;
};

export const cancelSubscription = async () => {
  const { data } = await axiosConf.post("/api/subscription/cancel");
  return data;
};

export const resumeSubscription = async () => {
  const { data } = await axiosConf.post("/api/subscription/resume");
  return data;
};

export const updatePaymentMethod = async (paymentMethodId) => {
  const { data } = await axiosConf.post("/api/subscription/payment-method", {
    payment_method_id: paymentMethodId
  });
  return data;
};

export const getSubscriptionHistory = async (page = 1) => {
  const { data } = await axiosConf.get(`/api/subscription/history?page=${page}`);
  return data;
};
