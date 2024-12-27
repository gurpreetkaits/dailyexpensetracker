import axiosConf from "../config/axiosConf.js";

export const getTransactions = async (dateFilter) => {
  const data = await axiosConf.get(`/api/transactions?${objectToQueryString({filter: dateFilter.toLowerCase()})}`);
  return data;
};
export const saveTransaction = async (transaction) => {
  const { data } = await axiosConf.post("/api/transactions", transaction);
  return data;
};

export const updateTransaction = async (transaction, id) => {
  const { data } = await axiosConf.put(`/api/transactions/${id}`, transaction);
  return data;
};

export const deleteTransaction = async (id) => {
  const { data } = await axiosConf.delete(`/api/transactions/${id}`);
  return data;
};

export const getTransactionById = async (id) => {
  const { data } = await axiosConf.get(`/api/transactions/${id}`);
  return data;
};

export const getTransactionStats = async (filters) => {
  const { data } = await axiosConf.get(
    `/api/transactions/stats?${objectToQueryString(filters)}`
  );
  return data;
};

function objectToQueryString(obj) {
  return Object.keys(obj)
    .filter((key) => obj[key] !== null && obj[key] !== undefined) // Remove null/undefined
    .map((key) => `${encodeURIComponent(key)}=${encodeURIComponent(obj[key])}`)
    .join("&");
}
