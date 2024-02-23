import { Box, Stack, Typography } from "@mui/material";
import { SingleEventDto } from "../../../../infrastructure/generated/openapi";
import { DateIndicator } from "../../date-indicator";
import { DateTimeIndicator } from "../../date-time-indicator";
import { LocationIndicator } from "../../location-indicator";
import { textFormatter } from "../../text-formatter";

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
            start={props?.singleEvent?.start}
            end={props.singleEvent?.end}
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
                }}
                src={props.singleEvent?.image?.fileUrl}
              />
            </Box>
            <Stack>
              <Typography variant="h5">{props.singleEvent?.titleDe}</Typography>
              <DateTimeIndicator
                start={props.singleEvent?.start}
                end={props.singleEvent?.end}
              />
              <LocationIndicator
                eventLocation={props.singleEvent?.eventLocation}
              />
            </Stack>
            <Typography
              component="span"
              variant={props.isMobileView ? "body2" : "body1"}
            >
              {textFormatter(props.singleEvent?.descriptionDe ?? "")}
            </Typography>
          </Box>
        </Box>
      </Box>
    </Box>
  );
};
