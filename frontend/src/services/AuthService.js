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
    localStorage.setItem("gCode", JSON.stringify(code));

    const { data } = await axios.post("https://oauth2.googleapis.com/token", {
      code,
      client_id: import.meta.env.VITE_GOOGLE_CLIENT,
      client_secret: import.meta.env.VITE_GOOGLE_SECRET,
      redirect_uri: import.meta.env.VITE_APP_URL,
      grant_type: "authorization_code",
    });

    if (data) {
      const accessToken = data.access_token;

      // Fetch user details using the access token
      const userObj = await axios.get(
        "https://www.googleapis.com/oauth2/v3/userinfo",
        {
          headers: {
            Authorization: `Bearer ${accessToken}`,
          },
        }
      );

      if (userObj && userObj.data) {
        localStorage.setItem("user", JSON.stringify(userObj.data));
        return userObj.data;
      }
    }
  } catch (e) {
    console.error("Failed to exchange token", e);
  }
};

export const logoutApi = async () => {
  await getCsrfToken();
  const { data } = await axiosConf.post('/api/logout');
  return data;
};
