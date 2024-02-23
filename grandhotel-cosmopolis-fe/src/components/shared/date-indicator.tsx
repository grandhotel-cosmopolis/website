import { Stack, Typography } from "@mui/material";

type DateIndicatorProps = {
  readonly start?: Date;
  readonly end?: Date;
};

export const DateIndicator = (props: DateIndicatorProps) => {
  if (props.start?.getDate() === props.end?.getDate()) {
    return <SingleDateIndicator date={props.start} />;
  }
  return (
    <Stack>
      <SingleDateIndicator date={props.start} />
      <Stack alignItems="center">
        <Typography variant="h4">-</Typography>
      </Stack>
      <SingleDateIndicator date={props.end} />
    </Stack>
  );
};

type SingleDateIndicatorProps = {
  readonly date?: Date;
};

const SingleDateIndicator = (props: SingleDateIndicatorProps) => {
  return (
    <Stack alignItems="center">
      <Typography variant="overline" lineHeight={1.2}>
        {props.date?.toLocaleDateString("de-DE", {
          month: "short",
        })}
      </Typography>
      <Typography lineHeight={1.2}>{props.date?.getDate()}</Typography>
      <Typography variant="caption">{props.date?.getFullYear()}</Typography>
    </Stack>
  );
};
