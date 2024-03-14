import { Typography } from "@mui/material";
import { SingleEventDto } from "../../infrastructure/generated/openapi";
import { ReactNode } from "react";
import { Variant } from "@mui/material/styles/createTypography";

type SingleEventTypographyProps = {
  readonly children?: ReactNode;
  readonly singleEvent?: SingleEventDto;
  readonly cancelled?: boolean;
  readonly variant?: Variant | "inherit";
  readonly spanComponent?: boolean;
  readonly lineHeight?: number;
};

export const SingleEventTypography = (props: SingleEventTypographyProps) => {
  return (
    <Typography
      sx={(theme) => ({
        textDecoration:
          !!props.singleEvent?.exception?.cancelled || !!props.cancelled
            ? "line-through"
            : undefined,
        color:
          !!props.singleEvent?.exception?.cancelled || !!props.cancelled
            ? theme.palette.text.disabled
            : undefined,
      })}
      variant={props.variant}
      component={!!props.spanComponent ? "span" : "p"}
      lineHeight={props.lineHeight}
    >
      {props.children}
    </Typography>
  );
};
