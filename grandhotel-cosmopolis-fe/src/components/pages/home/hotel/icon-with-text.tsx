import { Box, Typography } from "@mui/material";
import { useIsMobileView } from "../../../hooks/screen-sizes/use-is-mobile-view";
import { ReactNode } from "react";

type IconWithTextProps = {
  readonly icon: ReactNode;
  readonly text: string;
  readonly align: "left" | "right";
};

export const IconWithText = (props: IconWithTextProps) => {
  const isMobileView = useIsMobileView();
  return (
    <Box
      sx={{
        display: "flex",
        flexDirection: "column",
        alignItems: props.align === "left" ? "flex-start" : "flex-end",
      }}
    >
      {props.icon}
      {!isMobileView && (
        <Typography sx={(theme) => ({ color: theme.palette.primary.main })}>
          {props.text}
        </Typography>
      )}
    </Box>
  );
};
