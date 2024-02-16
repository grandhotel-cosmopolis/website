import axios from "axios";
import {
  Configuration,
  EventApi,
  EventLocationApi,
  FileApi,
  LoginApi,
  UserApi,
} from "./generated/openapi";
import { AxiosRequestTransformer } from "axios";
import { dateTransformer } from "./axios-date-transformer";

const basePath = "http://127.0.0.1:8000";

const customConfiguration = new Configuration({
  basePath: basePath,
});

const defaultTransformers = (): AxiosRequestTransformer[] => {
  const { transformRequest } = axios.defaults;
  if (!transformRequest) {
    return [];
  } else if (transformRequest instanceof Array) {
    return transformRequest;
  } else {
    return [transformRequest];
  }
};

const axiosInstance = axios.create({
  baseURL: basePath,
  withCredentials: true,
  withXSRFToken: true,
  transformRequest: [...defaultTransformers(), dateTransformer],
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

export const eventApi = new EventApi(
  customConfiguration,
  basePath,
  axiosInstance
);

export const fileApi = new FileApi(
  customConfiguration,
  basePath,
  axiosInstance
);

export const eventLocationApi = new EventLocationApi(
  customConfiguration,
  basePath,
  axiosInstance
);
