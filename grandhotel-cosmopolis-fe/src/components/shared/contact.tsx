import { Stack } from "@mui/material";
import { useIsTabletView } from "../hooks/screen-sizes/use-is-tablet-view";
import { Phone } from "./phone";
import { Mail } from "./mail";

export const Contact = () => {
  const isTabletView = useIsTabletView();
  return (
    <Stack
      spacing={isTabletView ? 0 : 2}
      direction={isTabletView ? "column" : "row"}
      alignItems="center"
    >
      <Phone align={isTabletView ? "row" : "column"} />
      <Mail align={isTabletView ? "row" : "column"} />
    </Stack>
  );
};
