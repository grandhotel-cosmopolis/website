import { Box } from "@chakra-ui/react";
import { Outlet } from "react-router-dom";

export const BasePage = () => {
  return (
    <Box minH="100vh">
      <Outlet />
    </Box>
  );
};
