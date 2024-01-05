import axios from "axios";
import { Configuration, LoginApi, UserApi } from "./generated/openapi";

const basePath = "http://127.0.0.1:8000";

const customConfiguration = new Configuration({
  basePath: basePath,
});

const axiosInstance = axios.create({
  baseURL: basePath,
  withCredentials: true,
  withXSRFToken: true,
});

export const retrieveCsrfToken = () => {
  return axios.get(`${basePath}/sanctum/csrf-cookie`, {
    withCredentials: true,
    withXSRFToken: true,
  });
};

export const loginClient = new LoginApi(
  customConfiguration,
  basePath,
  axiosInstance
);

export const userClient = new UserApi(
  customConfiguration,
  basePath,
  axiosInstance
);
