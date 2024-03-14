import { Stack } from "@mui/material";
import {
  EventLocationDto,
  SingleEventDto,
} from "../../../infrastructure/generated/openapi";
import { SingleEventTypography } from "../single-event-typography";
import ArrowRightAltIcon from "@mui/icons-material/ArrowRightAlt";

type EventLocationProps = {
  readonly singleEvent?: SingleEventDto;
};

export const EventLocation = (props: EventLocationProps) => {
  if (!!props.singleEvent?.exception?.eventLocation) {
    return (
      <Stack direction="row" spacing={1}>
        <SimpleEventLocation
          cancelled={true}
          eventLocation={props.singleEvent.eventLocation}
        />
        <Stack justifyContent="center">
          <ArrowRightAltIcon />
        </Stack>
        <SimpleEventLocation
          cancelled={!!props.singleEvent.exception.cancelled}
          eventLocation={props.singleEvent.exception.eventLocation}
        />
      </Stack>
    );
  }
  return (
    <SimpleEventLocation
      cancelled={!!props.singleEvent?.exception?.cancelled}
      eventLocation={props.singleEvent?.eventLocation}
    />
  );
};

type SimpleEventLocationProps = {
  readonly cancelled?: boolean;
  readonly eventLocation?: EventLocationDto;
};

const SimpleEventLocation = (props: SimpleEventLocationProps) => {
  return (
    <Stack>
      <SingleEventTypography cancelled={!!props.cancelled}>
        {props.eventLocation?.name}
      </SingleEventTypography>
      <SingleEventTypography cancelled={!!props.cancelled} variant="caption">
        {props.eventLocation?.street}
      </SingleEventTypography>
      <SingleEventTypography cancelled={!!props.cancelled} variant="caption">
        {props.eventLocation?.city}
      </SingleEventTypography>
      <SingleEventTypography cancelled={!!props.cancelled} variant="caption">
        {props.eventLocation?.additionalInformation}
      </SingleEventTypography>
    </Stack>
  );
};
