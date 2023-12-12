import { Theme, useMediaQuery } from "@mui/material";

export const useIsMobileView = () => {
  return useMediaQuery((theme: Theme) => theme.breakpoints.down("sm"));
};
