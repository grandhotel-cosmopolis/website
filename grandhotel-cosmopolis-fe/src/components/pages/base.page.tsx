import { Box } from "@mui/material";
import { Outlet } from "react-router-dom";
import { Header } from "../shared/header/header";

export const BasePage = () => {
  return (
    <Box>
      <Header />
      <Box
        minHeight="100vh"
        display="flex"
        justifyContent="center"
        alignItems="center"
      >
        <Outlet />
      </Box>
    </Box>
  );
};
