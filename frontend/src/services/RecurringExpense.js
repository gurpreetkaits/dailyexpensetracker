import axios from '../config/axiosConf.js'
export const getRecurringExpenses = async () => {
    try {
       const {data} = await axios.get('/api/recurring-expenses')
        return data
    }catch (error) {
      throw error
    }
}

export const getRecurringExpenseById = (id) => {
    return axios.get(`/api/recurring-expenses/${id}`)
}

export const createRecurringExpense = (expense) => {
    return axios.post('/api/recurring-expenses', expense)
}

export const updateRecurringExpense = (id, expense) => {
    return axios.put(`/api/recurring-expenses/${id}`, expense)
}

export const deleteRecurringExpense = (id) => {
    return axios.delete(`/api/recurring-expenses/${id}`)
}
