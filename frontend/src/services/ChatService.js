import axiosConf from "../config/axiosConf.js";

export const getChats = async () => {
  const data = await axiosConf.get("/api/chat");
  return data;
};

export const sendMessage = async (message) => {
  const data = await axiosConf.post("/api/chat", message);
  return data;
};


