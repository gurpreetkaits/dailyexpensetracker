import api from "../config/axiosConf";

export const getDashboardStats = () => {
    return api.get("/api/dashboard");
};
