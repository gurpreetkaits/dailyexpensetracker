import api from "../config/axiosConf";

export const getFeedbacks = () => {
    return api.get("/api/feedback");
};
