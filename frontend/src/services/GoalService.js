import api from "../config/axiosConf";

export const getGoals = () => {
  return api.get("/api/goals");
};

export const getGoalById = (id) => {
  return api.get(`/api/goals/${id}`);
};

export const createGoal = (data) => {
  return api.post("/api/goals", data);
};

export const updateGoal = (id, data) => {
  return api.put(`/api/goals/${id}`, data);
};

export const deleteGoal = (id) => {
  return api.delete(`/api/goals/${id}`);
};

export const updateGoalProgress = (id, saved) => {
  return api.patch(`/api/goals/${id}/progress`, { saved });
};
