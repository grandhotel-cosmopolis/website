import { Stack, Typography } from "@mui/material";
import { EventLocationDto } from "../../infrastructure/generated/openapi";

type LocationIndicatorProps = {
  readonly eventLocation?: EventLocationDto;
};

export const LocationIndicator = (props: LocationIndicatorProps) => {
  return (
    <Stack direction="row" spacing={1}>
      <Typography>Wo:</Typography>
      <Stack>
        <Typography>{props.eventLocation?.name}</Typography>
        <Typography variant="caption">{props.eventLocation?.street}</Typography>
        <Typography variant="caption">{props.eventLocation?.city}</Typography>
        <Typography variant="caption">
          {props.eventLocation?.additionalInformation}
        </Typography>
      </Stack>
    </Stack>
  );
};
