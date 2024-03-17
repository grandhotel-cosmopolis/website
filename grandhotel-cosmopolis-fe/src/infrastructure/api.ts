import axios from "axios";
import {
  Configuration,
  EventApi,
  EventLocationApi,
  FileApi,
  LoginApi,
  UserApi,
} from "./generated/openapi";
import { getApiBaseAddress } from "./stage-provider";

const customConfiguration = new Configuration({
  basePath: getApiBaseAddress(),
});

const axiosInstance = axios.create({
  baseURL: getApiBaseAddress(),
  withCredentials: true,
  withXSRFToken: true,
});

axiosInstance.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    // TODO implement rememberMe or redirect to login
    return Promise.reject(error);
  }
);

export const retrieveCsrfToken = () => {
  return axios.get(`${getApiBaseAddress()}/sanctum/csrf-cookie`, {
    withCredentials: true,
    withXSRFToken: true,
  });
};

export const loginClient = new LoginApi(
  customConfiguration,
  getApiBaseAddress(),
  axiosInstance
);

export const userClient = new UserApi(
  customConfiguration,
  getApiBaseAddress(),
  axiosInstance
);

export const eventApi = new EventApi(
  customConfiguration,
  getApiBaseAddress(),
  axiosInstance
);

export const fileApi = new FileApi(
  customConfiguration,
  getApiBaseAddress(),
  axiosInstance
);

export const eventLocationApi = new EventLocationApi(
  customConfiguration,
  getApiBaseAddress(),
  axiosInstance
);
