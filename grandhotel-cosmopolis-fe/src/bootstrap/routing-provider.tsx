import { Navigate, Route, Routes } from "react-router-dom";
import { BasePage } from "../components/pages/base.page";
import { Home } from "../components/pages/home.page";
import { Login } from "../components/pages/internal/login.page";
import { AuthenticationProvider } from "./authentication-provider";
import { InternalBasePage } from "../components/pages/internal/internal-base.page";
import { Events } from "../components/pages/internal/events.page";
import { Dashboard } from "../components/pages/internal/dashboard.page";
import { Administration } from "../components/pages/internal/administration.page";

export const RoutingProvider = () => {
  return (
    <Routes>
      <Route path="/" element={<BasePage />}>
        <Route index element={<Home />} />
        <Route path="/internal/login" element={<Login />} />
      </Route>
      <Route
        path="/internal"
        element={
          <AuthenticationProvider requiredPermission={[]}>
            <InternalBasePage />
          </AuthenticationProvider>
        }
      >
        <Route index element={<Dashboard />} />
        <Route path="/internal/user-management" element={<Administration />} />
        <Route path="/internal/events" element={<Events />} />
      </Route>
      <Route path="/internal/*" element={<Navigate to="/internal" replace />} />
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
};
