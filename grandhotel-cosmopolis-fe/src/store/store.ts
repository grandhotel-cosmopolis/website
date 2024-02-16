import { action, createStore, createTypedHooks } from "easy-peasy";
import { StoreModel } from "./store-model";

export const store = createStore<StoreModel>({
  user: undefined,
  setUser: action((state, user) => {
    state.user = user;
  }),
});

const typedHooks = createTypedHooks<StoreModel>();

export const useStoreActions = typedHooks.useStoreActions;
export const useStoreDispatch = typedHooks.useStoreDispatch;
export const useStoreState = typedHooks.useStoreState;
