import { defineStore } from 'pinia'
import { getUserWallets, createWallet, updateWallet, deleteWallet, getWalletTransactions, transferBetweenWallets, getWalletBalanceHistory } from '../services/WalletService'

export const useWalletStore = defineStore('wallet', {
    state: () => ({
        wallets: [],
        loading: false,
        pagination: {
            current_page: 1,
            per_page: 10,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
            links: []
        },
        transactions: [],
        transactionsPagination: {
            current_page: 1,
            per_page: 10,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0,
            links: []
        },
        balanceHistory: {},
        balanceHistoryLoading: false
    }),

    actions: {
        async fetchWallets(page = 1) {
            this.loading = true
            try {
                const response = await getUserWallets(page)
                this.wallets = response.data || []
                this.pagination = {
                    current_page: response.current_page || 1,
                    per_page: response.per_page || 10,
                    total: response.total || 0,
                    last_page: response.last_page || 1,
                    from: response.from || 0,
                    to: response.to || 0,
                    links: response.links || []
                }
                return response
            } catch (error) {
                console.error('Error fetching wallets:', error)
                this.wallets = []
                this.pagination = {
                    current_page: 1,
                    per_page: 10,
                    total: 0,
                    last_page: 1,
                    from: 0,
                    to: 0,
                    links: []
                }
                return null
            } finally {
                this.loading = false
            }
        },

        async addWallet(walletData) {
            try {
                const response = await createWallet(walletData)
                await this.fetchWallets(this.pagination.current_page)
                return response
            } catch (error) {
                console.error('Error adding wallet:', error)
                throw error
            }
        },

        async updateWallet(id, walletData) {
            try {
                const response = await updateWallet(id, walletData)
                await this.fetchWallets(this.pagination.current_page)
                return response
            } catch (error) {
                console.error('Error updating wallet:', error)
                throw error
            }
        },

        async removeWallet(id) {
            try {
                const response = await deleteWallet(id)
                await this.fetchWallets(this.pagination.current_page)
                return response
            } catch (error) {
                console.error('Error removing wallet:', error)
                throw error
            }
        },

        async fetchWalletTransactions(walletId, page = 1) {
            try {
                const response = await getWalletTransactions(walletId, page)
                this.transactions = response.data || []
                this.transactionsPagination = {
                    current_page: response.current_page || 1,
                    per_page: response.per_page || 10,
                    total: response.total || 0,
                    last_page: response.last_page || 1,
                    from: response.from || 0,
                    to: response.to || 0,
                    links: response.links || []
                }
                return response
            } catch (error) {
                console.error('Error fetching wallet transactions:', error)
                this.transactions = []
                this.transactionsPagination = {
                    current_page: 1,
                    per_page: 10,
                    total: 0,
                    last_page: 1,
                    from: 0,
                    to: 0,
                    links: []
                }
                return null
            }
        },

        async transferBetweenWallets(transferData) {
            try {
                const response = await transferBetweenWallets(transferData)
                // Refresh both wallets after transfer
                await this.fetchWallets(this.pagination.current_page)
                // Refresh balance history for both wallets
                await Promise.all([
                    this.fetchWalletBalanceHistory(transferData.fromWalletId),
                    this.fetchWalletBalanceHistory(transferData.toWalletId)
                ])
                return response
            } catch (error) {
                console.error('Error transferring between wallets:', error)
                throw error
            }
        },

        async fetchWalletBalanceHistory(walletId, period = 'month') {
            this.balanceHistoryLoading = true
            try {
                const response = await getWalletBalanceHistory(walletId, period)
                // Store both data and pagination info
                this.balanceHistory[walletId] = {
                    data: response.data || [],
                    pagination: {
                        current_page: response.current_page || 1,
                        per_page: response.per_page || 10,
                        total: response.total || 0,
                        last_page: response.last_page || 1,
                        from: response.from || 0,
                        to: response.to || 0,
                        links: response.links || []
                    }
                }
                return response
            } catch (error) {
                console.error('Error fetching wallet balance history:', error)
                this.balanceHistory[walletId] = {
                    data: [],
                    pagination: {
                        current_page: 1,
                        per_page: 10,
                        total: 0,
                        last_page: 1,
                        from: 0,
                        to: 0,
                        links: []
                    }
                }
                throw error
            } finally {
                this.balanceHistoryLoading = false
            }
        }
    }
})
