import { useCallback } from "react";
import { Permissions } from "../../infrastructure/generated/openapi";
import { useStoreState } from "../../store/store";

export const useHasPermission = () => {
  const user = useStoreState((state) => state.user);

  const hasPermission = useCallback(
    (permission: Permissions) => {
      return user?.permissions?.includes(permission) ?? false;
    },
    [user]
  );

  return hasPermission;
};
