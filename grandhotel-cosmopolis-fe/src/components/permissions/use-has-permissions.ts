import { useCallback } from "react";
import { useHasPermission } from "./use-has-permission";
import { Permissions } from "../../infrastructure/generated/openapi";

export const useHasPermissions = () => {
  const hasPermission = useHasPermission();

  const hasPermissions = useCallback(
    (permissions: Permissions[]) => {
      return permissions.reduce(
        (prev, current) => hasPermission(current) && prev,
        true
      );
    },
    [hasPermission]
  );
  return hasPermissions;
};
