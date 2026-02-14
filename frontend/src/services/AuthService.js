import axiosConf from "../config/axiosConf";
import axios from "axios";
const getCsrfToken = () => axiosConf.get("/sanctum/csrf-cookie");

export const login = async (user) => {

  await getCsrfToken();
  const {data} = await axiosConf.post("/api/login", user);
  console.log(data)
  localStorage.setItem(
    "user",
    JSON.stringify({ name: data.name, email: data.email })
  );
  return data;
};

export const register = async (user) => {
  await getCsrfToken();
  const { data } = await axiosConf.post("/api/register", user);
  return data;
};

export const verifyToken = async () => {
  const { data } = await axiosConf.get("/api/user");
  localStorage.setItem(
    "user",
    JSON.stringify({
      name: data.name,
      email: data.email,
      since: data.created_at,
        is_admin: data.is_admin,
    })
  );
  return data;
};

export const submitSurvey = async (surveyData) => {
  const { data } = await axiosConf.post('/api/user/survey', surveyData);
  return data;
};

// Third Party

export const getGoogleUserInfo = async (code) => {
  try {
    // Send code to backend - token exchange happens server-side
    // This keeps client_secret secure on the server
    await getCsrfToken();
    const { data } = await axiosConf.post("/api/auth/google/callback", {
      code,
      redirect_uri: import.meta.env.VITE_APP_URL,
    });

    if (data && data.user) {
      localStorage.setItem("user", JSON.stringify(data.user));
      return data.user;
    }
  } catch (e) {
    console.error("Failed to exchange token", e);
    throw e;
  }
};

export const logoutApi = async () => {
  await getCsrfToken();
  const { data } = await axiosConf.post('/api/logout');
  return data;
};
