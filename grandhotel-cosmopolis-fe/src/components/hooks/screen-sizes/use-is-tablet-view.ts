import { Theme, useMediaQuery } from "@mui/material";

export const useIsTabletView = () => {
  return useMediaQuery((theme: Theme) => theme.breakpoints.down("md"));
};
