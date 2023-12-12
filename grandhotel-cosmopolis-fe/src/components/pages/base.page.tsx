import { Box } from "@mui/material";
import { Outlet } from "react-router-dom";
import { Header } from "../shared/header/header";

export const BasePage = () => {
  return (
    <Box>
      <Header />
      <Box mt={2}>
        <Outlet />
      </Box>
    </Box>
  );
};
