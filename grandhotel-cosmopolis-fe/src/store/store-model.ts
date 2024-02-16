import { Action } from "easy-peasy";
import { UserDto } from "../infrastructure/generated/openapi";

export interface StoreModel {
  user?: UserDto;
  setUser: Action<StoreModel, UserDto>;
}
