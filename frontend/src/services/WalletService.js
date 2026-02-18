import api from "../config/axiosConf.js";

export const getUserWallets = async (page = 1) => {
    try {
        const response = await api.get(`/api/wallets?page=${page}`)
        return response.data
    } catch (error) {
        console.error('Error in getUserWallets:', error)
        throw error
    }
}

export const createWallet = async (walletData) => {
    try {
        const response = await api.post('/api/wallets', walletData)
        return response.data
    } catch (error) {
        console.error('Error in createWallet:', error)
        throw error
    }
}

export const updateWallet = async (id, walletData) => {
    try {
        const response = await api.put(`/api/wallets/${id}`, walletData)
        return response.data
    } catch (error) {
        console.error('Error in updateWallet:', error)
        throw error
    }
}

export const deleteWallet = async (id) => {
    try {
        const response = await api.delete(`/api/wallets/${id}`)
        return response.data
    } catch (error) {
        console.error('Error in deleteWallet:', error)
        throw error
    }
}

export const getWalletTransactions = async (walletId, page = 1) => {
    try {
        const response = await api.get(`/api/wallets/${walletId}/transactions?page=${page}`)
        return response.data
    } catch (error) {
        console.error('Error in getWalletTransactions:', error)
        throw error
    }
}

export const transferBetweenWallets = async (transferData) => {
    try {
        const response = await api.post('/api/wallets/transfer', transferData)
        return response.data
    } catch (error) {
        console.error('Error in transferBetweenWallets:', error)
        throw error
    }
}

export const getWalletBalanceHistory = async (walletId, period = 'month') => {
    try {
        const response = await api.get(`/api/wallets/${walletId}/balance-history?period=${period}`)
        return response.data
    } catch (error) {
        console.error('Error in getWalletBalanceHistory:', error)
        throw error
    }
}

export const getWalletTransfers = async (page = 1) => {
    try {
        const response = await api.get(`/api/wallets/transfers?page=${page}`)
        return response.data
    } catch (error) {
        console.error('Error in getWalletTransfers:', error)
        throw error
    }
}
