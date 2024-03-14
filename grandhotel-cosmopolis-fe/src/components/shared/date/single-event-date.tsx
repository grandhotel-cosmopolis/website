import { Stack } from "@mui/material";
import { SingleEventDto } from "../../../infrastructure/generated/openapi";
import { ReactElement } from "react";
import { SingleEventTypography } from "../single-event-typography";
import ArrowRightAltIcon from "@mui/icons-material/ArrowRightAlt";

type SingleEventDateProps = {
  readonly singleEvent?: SingleEventDto;
};

export const SingleEventDate = (props: SingleEventDateProps) => {
  if (
    !!props.singleEvent?.exception?.start ||
    !!props.singleEvent?.exception?.end
  ) {
    // Rescheduled
    let newDate: ReactElement;
    if (
      !!props.singleEvent.exception.start &&
      !props.singleEvent.exception.end
    ) {
      newDate = (
        <DateWrapper
          start={props.singleEvent.exception.start}
          end={props.singleEvent.end}
          cancelled={!!props.singleEvent.exception.cancelled}
          singleEvent={props.singleEvent}
        />
      );
    } else if (
      !props.singleEvent.exception.start &&
      !!props.singleEvent.exception.end
    ) {
      newDate = (
        <DateWrapper
          start={props.singleEvent.start}
          end={props.singleEvent.exception.end}
          cancelled={!!props.singleEvent.exception.cancelled}
          singleEvent={props.singleEvent}
        />
      );
    } else {
      newDate = (
        <DateWrapper
          start={props.singleEvent.exception.start ?? undefined}
          end={props.singleEvent.exception.end ?? undefined}
          cancelled={!!props.singleEvent.exception.cancelled}
          singleEvent={props.singleEvent}
        />
      );
    }
    return (
      <Stack direction="row" spacing={1}>
        <DateWrapper
          start={props.singleEvent.start}
          end={props.singleEvent.end}
          cancelled={true}
          singleEvent={props.singleEvent}
        />
        <Stack justifyContent="center">
          <ArrowRightAltIcon />
        </Stack>
        {newDate}
      </Stack>
    );
  }

  return (
    <DateWrapper
      start={props.singleEvent?.start}
      end={props.singleEvent?.end}
      cancelled={!!props.singleEvent?.exception?.cancelled}
      singleEvent={props.singleEvent}
    />
  );
};

type DateWrapperProps = {
  readonly singleEvent?: SingleEventDto;
  readonly start?: Date;
  readonly end?: Date;
  readonly cancelled?: boolean;
};

const DateWrapper = (props: DateWrapperProps) => {
  if (!!props.start && !props.end) {
    return (
      <SingleDate
        singleEvent={props.singleEvent}
        date={props.start}
        cancelled={!!props.cancelled}
      />
    );
  }
  if (!!props.end && !props.start) {
    return (
      <SingleDate
        singleEvent={props.singleEvent}
        date={props.end}
        cancelled={!!props.cancelled}
      />
    );
  }
  if (!!props.start && !!props.end) {
    return (
      <DoubleDate
        singleEvent={props.singleEvent}
        start={props.start}
        end={props.end}
        cancelled={!!props.cancelled}
      />
    );
  }
  return <></>;
};

type SingleDateProps = {
  readonly date: Date;
  readonly cancelled?: boolean;
  readonly singleEvent?: SingleEventDto;
};

const SingleDate = (props: SingleDateProps) => {
  return (
    <SingleEventTypography singleEvent={props.singleEvent}>
      {props.date.toLocaleDateString("de-DE")}
    </SingleEventTypography>
  );
};

type DoubleDateProps = {
  readonly start: Date;
  readonly end: Date;
  readonly cancelled?: boolean;
  readonly singleEvent?: SingleEventDto;
};

const DoubleDate = (props: DoubleDateProps) => {
  if (props.start.getDate() === props.end.getDate()) {
    return (
      <SingleEventTypography
        singleEvent={props.singleEvent}
        cancelled={!!props.cancelled}
      >
        <>
          {props.start.toLocaleDateString("de-DE", { dateStyle: "short" })}
          {", "}
          {props.start.toLocaleTimeString("de-DE", {
            hour: "2-digit",
            minute: "2-digit",
          })}{" "}
          -{" "}
          {props.end.toLocaleTimeString("de-DE", {
            hour: "2-digit",
            minute: "2-digit",
          })}
        </>
      </SingleEventTypography>
    );
  }
  return (
    <SingleEventTypography
      singleEvent={props.singleEvent}
      cancelled={!!props.cancelled}
    >
      <>
        {props.start.toLocaleString("de-DE", {
          dateStyle: "short",
          timeStyle: "short",
        })}{" "}
        -{" "}
        {props.end.toLocaleString("de-DE", {
          dateStyle: "short",
          timeStyle: "short",
        })}
      </>
    </SingleEventTypography>
  );
};
