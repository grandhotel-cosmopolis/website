import { Stack } from "@mui/material";
import { SingleEventTypography } from "./single-event-typography";
import { SingleEventDto } from "../../infrastructure/generated/openapi";

type DateIndicatorProps = {
  readonly start?: Date;
  readonly end?: Date;
  readonly singleEvent?: SingleEventDto;
};

export const DateIndicator = (props: DateIndicatorProps) => {
  if (props.start?.getDate() === props.end?.getDate()) {
    return (
      <SingleDateIndicator date={props.start} singleEvent={props.singleEvent} />
    );
  }
  return (
    <Stack>
      <SingleDateIndicator date={props.start} singleEvent={props.singleEvent} />
      <Stack alignItems="center">
        <SingleEventTypography variant="h4" singleEvent={props.singleEvent}>
          -
        </SingleEventTypography>
      </Stack>
      <SingleDateIndicator date={props.end} singleEvent={props.singleEvent} />
    </Stack>
  );
};

type SingleDateIndicatorProps = {
  readonly date?: Date;
  readonly singleEvent?: SingleEventDto;
};

const SingleDateIndicator = (props: SingleDateIndicatorProps) => {
  return (
    <Stack alignItems="center">
      <SingleEventTypography
        variant="overline"
        lineHeight={1.2}
        singleEvent={props.singleEvent}
      >
        {props.date?.toLocaleDateString("de-DE", {
          month: "short",
        })}
      </SingleEventTypography>
      <SingleEventTypography lineHeight={1.2} singleEvent={props.singleEvent}>
        {props.date?.getDate()}
      </SingleEventTypography>
      <SingleEventTypography variant="caption" singleEvent={props.singleEvent}>
        {props.date?.getFullYear()}
      </SingleEventTypography>
    </Stack>
  );
};
