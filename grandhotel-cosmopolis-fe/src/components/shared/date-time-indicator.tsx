import { Typography } from "@mui/material";
import { formatDateTimeRange } from "../../services/date-time.service";

type DateTimeIndicatorProps = {
  readonly start?: Date;
  readonly end?: Date;
};

export const DateTimeIndicator = (props: DateTimeIndicatorProps) => {
  return (
    <Typography>Wann: {formatDateTimeRange(props.start, props.end)}</Typography>
  );
};
