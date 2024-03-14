import { Box, Stack } from "@mui/material";
import { SingleEventDto } from "../../../../infrastructure/generated/openapi";
import { DateIndicator } from "../../date-indicator";
import { textFormatter } from "../../text-formatter";
import { SingleEventDate } from "../../date/single-event-date";
import { EventLocation } from "../../location/event-location";
import { SingleEventTypography } from "../../single-event-typography";

type SingleEventViewProps = {
  readonly singleEvent?: SingleEventDto;
  readonly isMobileView: boolean;
};

export const SingleEventView = (props: SingleEventViewProps) => {
  return (
    <Box>
      <Box
        display="flex"
        width="100%"
        flexDirection="row"
        sx={{ mb: 4, mt: 4, pr: 2 }}
      >
        <Box width="100px" sx={{ mr: 2 }}>
          <DateIndicator
            start={
              props.singleEvent?.exception?.start ?? props?.singleEvent?.start
            }
            end={props.singleEvent?.exception?.end ?? props.singleEvent?.end}
            singleEvent={props.singleEvent}
          />
        </Box>
        <Box display="flex" width="100%">
          <Box width="100%">
            <Box width="100%">
              <img
                style={{
                  float: "right",
                  width: props.isMobileView ? "100%" : "40%",
                  marginLeft: 32,
                  marginBottom: props.isMobileView ? 16 : 0,
                  filter: !!props.singleEvent?.exception?.cancelled
                    ? "grayscale(100%)"
                    : undefined,
                }}
                src={props.singleEvent?.image?.fileUrl}
              />
            </Box>
            <Stack spacing={2}>
              <SingleEventTypography
                variant="h5"
                singleEvent={props.singleEvent}
              >
                {props.singleEvent?.titleDe}
              </SingleEventTypography>
              <Stack>
                <SingleEventTypography
                  singleEvent={props.singleEvent}
                  variant="body2"
                  lineHeight={1}
                >
                  Wann:
                </SingleEventTypography>
                <SingleEventDate singleEvent={props.singleEvent} />
              </Stack>
              <Stack>
                <SingleEventTypography
                  singleEvent={props.singleEvent}
                  variant="body2"
                  lineHeight={1}
                >
                  Wo:
                </SingleEventTypography>
                <EventLocation singleEvent={props.singleEvent} />
              </Stack>
            </Stack>
            <SingleEventTypography
              spanComponent
              variant={props.isMobileView ? "body2" : "body1"}
              singleEvent={props.singleEvent}
            >
              {textFormatter(props.singleEvent?.descriptionDe ?? "")}
            </SingleEventTypography>
          </Box>
        </Box>
      </Box>
    </Box>
  );
};
