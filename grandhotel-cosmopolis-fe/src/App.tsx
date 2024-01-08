import { BrowserRouter } from "react-router-dom";
import { RoutingProvider } from "./bootstrap/routing-provider";
import { ThemeProvider } from "@emotion/react";
import theme from "./components/style/theme";
import { CssBaseline } from "@mui/material";
import { Suspense } from "react";
import { QueryClient, QueryClientProvider } from "react-query";

const queryClient = new QueryClient();

export default function App() {
  return (
    <Suspense fallback="...is loading">
      <QueryClientProvider client={queryClient}>
        <ThemeProvider theme={theme}>
          <CssBaseline />
          <BrowserRouter>
            <RoutingProvider />
          </BrowserRouter>
        </ThemeProvider>
      </QueryClientProvider>
    </Suspense>
  );
}
