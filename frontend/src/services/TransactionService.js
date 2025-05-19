import axiosConf from "../config/axiosConf.js";

export const getTransactions = async (dateFilter) => {
    return await axiosConf.get(`/api/transactions?${objectToQueryString({filter: dateFilter.toLowerCase()})}`);
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

export const searchTransactions = async (query, dateFilter) => {
    try {
        const response = await axiosConf.get(
            `/api/transactions/search?query=${encodeURIComponent(query)}`,
        )

        if (!response.ok) {
            throw new Error('Search request failed');
        }

        return await response.json();
    } catch (error) {
        console.error('Error searching transactions:', error);
        throw error;
    }
};

function objectToQueryString(obj) {
  return Object.keys(obj)
    .filter((key) => obj[key] !== null && obj[key] !== undefined) // Remove null/undefined
    .map((key) => `${encodeURIComponent(key)}=${encodeURIComponent(obj[key])}`)
    .join("&");
}


export const getCategoryTransactions = async (filters) => {
  const { data } = await axiosConf.get(`/api/get-transactions?${objectToQueryString(filters)}`);
  return data;
};

export const getActivityBarDataV2 = async (period, bar = null) => {
  let url = `/api/transactions/activity-bar-data-v2?period=${period}`
  if (bar) {
    url += `&bar=${encodeURIComponent(JSON.stringify(bar))}`
  }
  const { data } = await axiosConf.get(url)
  return data
}
