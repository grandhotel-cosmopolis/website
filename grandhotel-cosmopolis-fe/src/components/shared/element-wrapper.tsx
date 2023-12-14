import { Box } from "@mui/material";
import { ReactElement } from "react";

type ElementWrapperProps = {
  readonly fullWidthBackgroundColor?: string;
  readonly backgroundImage?: BackgroundImageProps;
  readonly children?: ReactElement;
};

type BackgroundImageProps = {
  readonly backgroundImage?: string;
  readonly backgroundSize?: string;
  readonly backgroundRepeat?: string;
  readonly backgroundPosition?: string;
};

export const ElementWrapper = (props: ElementWrapperProps) => {
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
        m={5}
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
