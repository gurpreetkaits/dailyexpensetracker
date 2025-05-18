import axiosConf from "../config/axiosConf.js";

export const getSubscriptionStatus = async () => {
    const { data } = await axiosConf.get("/api/polar/subscription/status");
    return data;
};

export const getCheckoutUrl = (type) => {
    if(type === 'monthly') {
        return import.meta.env.VITE_POLAR_CHECKOUT_URL_MONTHLY;
    } else if(type === 'yearly') {
        return import.meta.env.VITE_POLAR_CHECKOUT_URL_YEARLY;
    } else {
        return import.meta.env.VITE_POLAR_CHECKOUT_URL_MONTHLY;
    }
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

