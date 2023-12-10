import { Box, Button } from "@chakra-ui/react";
import { useNavigate } from "react-router-dom";

export const Test = () => {
  const navigate = useNavigate();

  return (
    <Box>
      Hello Test <Button onClick={() => navigate("/")}>To Home</Button>
    </Box>
  );
};
