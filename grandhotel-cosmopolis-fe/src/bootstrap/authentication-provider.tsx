import { ReactNode, useEffect } from "react";
import { useStoreState } from "../store/store";
import { Permissions } from "../infrastructure/generated/openapi";
import { useNavigate } from "react-router-dom";

type AuthenticatedRoutingProiderProps = {
  children: ReactNode;
  requiredPermission: Permissions[];
};

export const AuthenticationProvider = (
  props: AuthenticatedRoutingProiderProps
) => {
  const user = useStoreState((store) => store.user);
  const navigate = useNavigate();

  useEffect(() => {
    if (!user) {
      navigate("/internal/login");
    }
  }, [user]);

  return !!user &&
    props.requiredPermission.every((p) => user.permissions?.includes(p)) ? (
    <>{props.children}</>
  ) : (
    <></>
  );
};
