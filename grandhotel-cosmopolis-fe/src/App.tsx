import { BrowserRouter } from "react-router-dom";
import { RoutingProvider } from "./bootstrap/routing-provider";
import { ThemeProvider } from "@emotion/react";
import theme from "./components/style/theme";
import { CssBaseline } from "@mui/material";
import { Suspense } from "react";
import { QueryClient, QueryClientProvider } from "react-query";
import { LocalizationProvider } from "@mui/x-date-pickers";
import { AdapterDateFns } from "@mui/x-date-pickers/AdapterDateFns";
import { StoreProvider } from "easy-peasy";
import { store } from "./store/store";

const queryClient = new QueryClient();

export default function App() {
  return (
    <Suspense fallback="...is loading">
      <StoreProvider store={store}>
        <LocalizationProvider dateAdapter={AdapterDateFns}>
          <QueryClientProvider client={queryClient}>
            <ThemeProvider theme={theme}>
              <CssBaseline />
              <BrowserRouter>
                <RoutingProvider />
              </BrowserRouter>
            </ThemeProvider>
          </QueryClientProvider>
        </LocalizationProvider>
      </StoreProvider>
    </Suspense>
  );
}
