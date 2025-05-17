import axiosConf from "../config/axiosConf";

export const getYearlyComparison = async (previousYear, currentYear ) => {
  try {
    const { data } = await axiosConf.get(`api/stats/yearly-comparison?previous_year=${previousYear}&current_year=${currentYear}`);
    return data;
  } catch (e) {
    return Promise.reject(e);
  }
};
