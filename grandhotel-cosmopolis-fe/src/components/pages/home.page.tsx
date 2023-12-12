import { Box, Button } from "@chakra-ui/react";
import { useNavigate } from "react-router-dom";

export const Home = () => {
  const navigate = useNavigate();

  return (
    <Box>
      Wilkommen <Button onClick={() => navigate("/test")}>To Test</Button>
    </Box>
  );
};
