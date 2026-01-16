import axiosConf from "../config/axiosConf.js";

export const getTransactions = async (dateFilter) => {
    return await axiosConf.get(`/api/transactions?${objectToQueryString({filter: dateFilter.toLowerCase()})}`);
};

export const getPaginatedTransactions = async (page = 1, filters = {}) => {
    const queryParams = {
        page,
        ...filters
    };
    
    // Convert any filter values to lowercase if they're strings
    Object.keys(queryParams).forEach(key => {
        if (typeof queryParams[key] === 'string') {
            queryParams[key] = queryParams[key].toLowerCase();
        }
    });
    
    return await axiosConf.get(`/api/transactions/paginated?${objectToQueryString(queryParams)}`);
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

export const getDailyBarData = async (params = {}) => {
  const queryParams = new URLSearchParams()
  if (params.start_date) queryParams.append('start_date', params.start_date)
  if (params.end_date) queryParams.append('end_date', params.end_date)
  if (params.per_page) queryParams.append('per_page', params.per_page)

  const queryString = queryParams.toString()
  const url = `/api/transactions/daily-bar-data${queryString ? '?' + queryString : ''}`
  const { data } = await axiosConf.get(url)
  return data
}

export const getExportStatus = async () => {
  const { data } = await axiosConf.get('/api/export/status')
  return data
}

export const exportTransactions = async (params = {}) => {
  const queryParams = new URLSearchParams()
  queryParams.append('format', params.format || 'xlsx')
  if (params.start_date) queryParams.append('start_date', params.start_date)
  if (params.end_date) queryParams.append('end_date', params.end_date)
  if (params.type) queryParams.append('type', params.type)

  const response = await axiosConf.get(`/api/transactions/export?${queryParams.toString()}`, {
    responseType: 'blob'
  })

  // Check if response is an error (JSON instead of blob)
  const contentType = response.headers['content-type']
  if (contentType && contentType.includes('application/json')) {
    const text = await response.data.text()
    const error = JSON.parse(text)
    throw new Error(error.message || 'Export failed')
  }

  // Create download link
  const url = window.URL.createObjectURL(new Blob([response.data]))
  const link = document.createElement('a')
  link.href = url

  // Get filename from Content-Disposition header or use default
  const contentDisposition = response.headers['content-disposition']
  const ext = params.format === 'xlsx' ? 'xlsx' : params.format === 'csv' ? 'csv' : 'pdf'
  let filename = `transactions.${ext}`
  if (contentDisposition) {
    const filenameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/)
    if (filenameMatch && filenameMatch[1]) {
      filename = filenameMatch[1].replace(/['"]/g, '')
    }
  }

  link.setAttribute('download', filename)
  document.body.appendChild(link)
  link.click()
  link.remove()
  window.URL.revokeObjectURL(url)
}
