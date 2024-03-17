import { Box } from "@mui/material";
import { ReactElement } from "react";
import { useIsMobileView } from "../hooks/screen-sizes/use-is-mobile-view";

type ElementWrapperProps = {
  readonly fullWidthBackgroundColor?: string;
  readonly backgroundImage?: BackgroundImageProps;
  readonly children?: ReactElement;
  readonly dense?: boolean;
};

type BackgroundImageProps = {
  readonly backgroundImage?: string;
  readonly backgroundSize?: string;
  readonly backgroundRepeat?: string;
  readonly backgroundPosition?: string;
};

export const ElementWrapper = (props: ElementWrapperProps) => {
  const isMobileView = useIsMobileView();
  return (
    <Box
      width="100%"
      sx={{
        backgroundColor: props.fullWidthBackgroundColor ?? "inherit",
        ...props.backgroundImage,
      }}
      display="flex"
      justifyContent="center"
    >
      <Box
        my={props.dense ? 0 : isMobileView ? 2 : 5}
        mx={isMobileView ? 2 : 5}
        width="100%"
        sx={(theme) => ({
          maxWidth: theme.spacing(140),
        })}
        display="flex"
        justifyContent="center"
      >
        {props.children}
      </Box>
    </Box>
  );
};
