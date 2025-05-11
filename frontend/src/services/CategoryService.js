import axiosConf from "../config/axiosConf.js";

export const getCategories = async () => {
  const { data } = await axiosConf.get('/api/categories');
  return data;
};

export const getUserCategories = async () => {
    const { data } = await axiosConf.get('/api/user-categories');
    return data;
};

export const createCategory = async (category) => {
  const { data } = await axiosConf.post('/api/categories', category);
  return data;
};

export const updateCategory = async (id, category) => {
  const { data } = await axiosConf.put(`/api/categories/${id}`, category);
  return data;
};

export const deleteCategory = async (id) => {
  await axiosConf.delete(`/api/categories/${id}`);
  return true;
};
