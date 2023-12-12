import { BrowserRouter } from "react-router-dom";
import { RoutingProvider } from "./bootstrap/routing-provider";
import { ThemeProvider } from "@emotion/react";
import theme from "./components/style/theme";
import { CssBaseline } from "@mui/material";

export default function App() {
  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <BrowserRouter>
        <RoutingProvider />
      </BrowserRouter>
    </ThemeProvider>
  );
}
