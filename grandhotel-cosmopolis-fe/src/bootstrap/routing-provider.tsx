import { Route, Routes } from "react-router-dom";
import { BasePage } from "../components/pages/base.page";
import { Home } from "../components/pages/home.page";
import { Login } from "../components/pages/internal/login.page";
import { Dashboard } from "../components/pages/internal/dasboard.page";

export const RoutingProvider = () => {
  return (
    <Routes>
      <Route path="/" element={<BasePage />}>
        <Route index element={<Home />} />
        <Route path="/internal/login" element={<Login />} />
        <Route path="/internal/dashboard" element={<Dashboard />} />
      </Route>
    </Routes>
  );
};
