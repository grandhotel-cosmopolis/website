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
import { getApiBaseAddress } from "./stage-provider";

const customConfiguration = new Configuration({
  basePath: getApiBaseAddress(),
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
  baseURL: getApiBaseAddress(),
  withCredentials: true,
  withXSRFToken: true,
  transformRequest: [...defaultTransformers(), dateTransformer],
});

const isDate = (data: any): boolean => {
  return (
    !!data &&
    typeof data != "boolean" &&
    typeof data != "number" &&
    //@ts-ignore
    new Date(data) !== "Invalid Date" &&
    //@ts-ignore
    !isNaN(new Date(data))
  );
};

const transformDates = (data: any): any => {
  if (isDate(data)) {
    return new Date(data);
  }
  if (Array.isArray(data)) {
    return data.map((val) => transformDates(val));
  }
  if (typeof data == "object" && data != null) {
    return Object.fromEntries(
      Object.entries(data).map(([key, val]) => [key, transformDates(val)])
    );
  }
  return data;
};

axiosInstance.interceptors.response.use(
  (response) => {
    response.data = transformDates(response.data);
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
