import { BrowserRouter } from "react-router-dom";
import { RoutingProvider } from "./bootstrap/routing-provider";
import { ThemeProvider } from "@emotion/react";
import theme from "./components/style/theme";
import { CssBaseline } from "@mui/material";
import { Suspense } from "react";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { ReactQueryDevtools } from "@tanstack/react-query-devtools";
import { LocalizationProvider } from "@mui/x-date-pickers";
import { AdapterDateFns } from "@mui/x-date-pickers/AdapterDateFns";
import { StoreProvider } from "easy-peasy";
import { store } from "./store/store";
import de from "date-fns/locale/de";

const queryClient = new QueryClient();

export default function App() {
  return (
    <Suspense fallback="...is loading">
      <StoreProvider store={store}>
        <LocalizationProvider dateAdapter={AdapterDateFns} adapterLocale={de}>
          <QueryClientProvider client={queryClient}>
            <ThemeProvider theme={theme}>
              <CssBaseline />
              <BrowserRouter>
                <RoutingProvider />
              </BrowserRouter>
            </ThemeProvider>
            <ReactQueryDevtools initialIsOpen={false} />
          </QueryClientProvider>
        </LocalizationProvider>
      </StoreProvider>
    </Suspense>
  );
}
