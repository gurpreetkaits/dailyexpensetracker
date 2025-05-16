import axiosConf from "../config/axiosConf.js";

export const getSubscriptionStatus = async () => {
    const { data } = await axiosConf.get("/api/polar/subscription/status");
    return data;
};

export const getCheckoutUrl = () => {
    return import.meta.env.VITE_POLAR_CHECKOUT_URL;
};

export const verifyCheckoutSession = async (checkoutId) => {
    const { data } = await axiosConf.post("/api/polar/subscription/verify-session", {
        checkout_id: checkoutId
    });
    return data;
};

export const cancelSubscription = async () => {
    const { data } = await axiosConf.post("/api/polar/subscription/cancel");
    return data;
};

export const resumeSubscription = async () => {
    const { data } = await axiosConf.post("/api/polar/subscription/resume");
    return data;
};

export const updatePaymentMethod = async (paymentMethodId) => {
    const { data } = await axiosConf.post("/api/polar/subscription/payment-method", {
        payment_method_id: paymentMethodId
    });
    return data;
};

