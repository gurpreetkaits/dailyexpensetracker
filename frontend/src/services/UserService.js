import axiosConf from "../config/axiosConf.js";

/**
 * Get user activity data for the contribution graph
 * Returns a map of dates to transaction counts
 * Activity is based on days when user added expenses/transactions (by created_at)
 */
export const getUserActivityData = async () => {
  try {
    const { data } = await axiosConf.get('/api/user/activity');
    return data;
  } catch (error) {
    console.error('Failed to fetch activity data:', error);
    return { activity: {} };
  }
};

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
