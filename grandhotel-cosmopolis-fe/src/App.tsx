import { ChakraProvider } from "@chakra-ui/react";
import { BrowserRouter } from "react-router-dom";
import { RoutingProvider } from "./bootstrap/routing-provider";

export default function App() {
  return (
    <ChakraProvider>
      <BrowserRouter>
        <RoutingProvider />
      </BrowserRouter>
    </ChakraProvider>
  );
}
