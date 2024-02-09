import { AxiosRequestTransformer } from "axios";

export const dateTransformer: AxiosRequestTransformer = (data: any) => {
  if (isDate(data) || data instanceof Date) {
    // do your specific formatting here
    return new Date(data).toISOString();
  }
  if (Array.isArray(data)) {
    // @ts-ignore
    return data.map((val) => dateTransformer(val));
  }
  if (data instanceof FormData) {
    const formData: FormData = new FormData();
    // @ts-ignore
    data.forEach((val, key) => formData.append(key, dateTransformer(val)));
    return formData;
  }
  if (data instanceof File) {
    return data;
  }
  if (typeof data === "object" && data !== null) {
    return Object.fromEntries(
      // @ts-ignore
      Object.entries(data).map(([key, val]) => [key, dateTransformer(val)])
    );
  }

  return data;
};

const isDate = (data: any): boolean => {
  //@ts-ignore
  return new Date(data) !== "Invalid Date" && !isNaN(new Date(data));
};
