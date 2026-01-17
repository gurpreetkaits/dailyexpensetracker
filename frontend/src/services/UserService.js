import axiosConf from "../config/axiosConf.js";

/**
 * Get user activity data for the contribution graph
 * Returns a map of dates to transaction counts
 * Activity is based on days when user added expenses/transactions
 */
export const getUserActivityData = async () => {
  try {
    // Try dedicated activity endpoint first
    const { data } = await axiosConf.get('/api/user/activity');
    console.log('Activity from /api/user/activity:', data);
    return data;
  } catch (error) {
    console.log('Falling back to calculate from transactions');
    // If endpoint doesn't exist (404) or fails, calculate from transactions
    return await calculateActivityFromTransactions();
  }
};

/**
 * Calculate activity data from existing transactions
 * Groups transactions by date and counts them
 */
async function calculateActivityFromTransactions() {
  const activity = {};

  try {
    const oneYearAgo = new Date();
    oneYearAgo.setFullYear(oneYearAgo.getFullYear() - 1);
    const startDate = oneYearAgo.toISOString().split('T')[0];
    const endDate = new Date().toISOString().split('T')[0];

    // Try to get daily bar data (already grouped by date)
    try {
      const { data } = await axiosConf.get(`/api/transactions/daily-bar-data?start_date=${startDate}&end_date=${endDate}&per_page=365`);
      console.log('Daily bar data response:', data);

      const dailyData = data.data || data || [];

      if (Array.isArray(dailyData) && dailyData.length > 0) {
        dailyData.forEach(day => {
          // Handle different possible field names
          const date = day.date || day.transaction_date;
          const count = day.count || day.total || day.transaction_count ||
                       (day.expense_count || 0) + (day.income_count || 0) || 1;

          if (date && count > 0) {
            activity[date] = count;
          }
        });

        console.log('Calculated activity from daily bar:', activity);
        if (Object.keys(activity).length > 0) {
          return { activity };
        }
      }
    } catch (e) {
      console.log('Daily bar data failed:', e.message);
    }

    // Alternative: Fetch all transactions and group manually
    try {
      const { data } = await axiosConf.get(`/api/transactions?filter=yearly`);
      console.log('Transactions response:', data);

      const transactions = data.transactions || data.data || data || [];

      if (Array.isArray(transactions) && transactions.length > 0) {
        transactions.forEach(transaction => {
          const date = (transaction.transaction_date || transaction.date || '')
            .split('T')[0]
            .split(' ')[0]; // Handle both ISO and datetime formats

          if (date) {
            activity[date] = (activity[date] || 0) + 1;
          }
        });

        console.log('Calculated activity from transactions:', activity);
      }
    } catch (e) {
      console.log('Transactions fetch failed:', e.message);
    }

    return { activity };
  } catch (error) {
    console.error('Failed to calculate activity:', error);
    return { activity };
  }
}

/**
 * Get user profile data
 */
export const getUserProfile = async () => {
  const { data } = await axiosConf.get('/api/user/profile');
  return data;
};

/**
 * Update user profile
 */
export const updateUserProfile = async (profileData) => {
  const { data } = await axiosConf.put('/api/user/profile', profileData);
  return data;
};
