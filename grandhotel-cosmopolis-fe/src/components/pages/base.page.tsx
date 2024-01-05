import { Box } from "@mui/material";
import { Outlet } from "react-router-dom";
import { Header } from "../shared/header/header";
import { Footer } from "../shared/footer/footer";

export const BasePage = () => {
  return (
    <Box display="flex" flexDirection="column" height="100vh">
      <Header />
      <Box
        pt={2}
        sx={(theme) => ({
          backgroundColor: theme.palette.secondary.main,
          flexGrow: 1,
        })}
      >
        <Outlet />
      </Box>
      <Footer />
    </Box>
  );
};
