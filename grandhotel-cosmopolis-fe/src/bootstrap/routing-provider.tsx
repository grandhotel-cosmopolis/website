import { Route, Routes } from "react-router-dom";
import { BasePage } from "../components/pages/base.page";
import { Home } from "../components/pages/home.page";

export const RoutingProvider = () => {
  return (
    <Routes>
      <Route path="/" element={<BasePage />}>
        <Route index element={<Home />} />
      </Route>
    </Routes>
  );
};
