import axiosConf from "../config/axiosConf";

export const fetchCurrencies = async () => {
  try {
    const { data } = await axiosConf.get("api/currencies");
    return data.data;
  } catch (e) {
    return Promise.reject(e);
  }
};

export const getCategories = async () => {
  try {
    const { data } = await axiosConf.get("api/categories");
    return data;
  } catch (e) {
    return Promise.reject(e);
  }
};

export const saveSettings = async (settings) => {
  try {
    const { data } = await axiosConf.post("api/settings", settings);
    return data;
  } catch (e) {
    return Promise.reject(e);
  }
};
export const getSettings = async () => {
  try {
    return await axiosConf.get("api/settings");
  } catch (e) {
    return Promise.reject(e);
  }
};

export const getStats = async (page) => {
  try {
    return await axiosConf.get(`api/stats?page=${page}`);
  } catch (e) {
    return Promise.reject(e);
  }
};

export const resetUserTransactions = async () => {
  return await axiosConf.delete('/api/user-setttings/transactions/reset');
};