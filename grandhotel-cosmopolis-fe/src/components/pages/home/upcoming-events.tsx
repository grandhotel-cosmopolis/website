import {
  Box,
  Card,
  CardContent,
  Divider,
  Stack,
  Typography,
  useTheme,
} from "@mui/material";
import { ElementWrapper } from "../../shared/element-wrapper";
import { useUpcomingEventsQuery } from "./upcoming-events/use-upcoming-events-query";
import { EventLocationDto } from "../../../infrastructure/generated/openapi";
import { useIsMobileView } from "../../hooks/screen-sizes/use-is-mobile-view";

export const UpcomingEvents = () => {
  const { data } = useUpcomingEventsQuery();
  const isMobileView = useIsMobileView();
  const theme = useTheme();

  const textFormatter = (text: string) => {
    return text.split("\n").map((str, i) => <p key={i}>{str}</p>);
  };

  return (
    <ElementWrapper>
      <Card sx={{ width: "100%" }}>
        <CardContent>
          <Box
            display="flex"
            justifyContent="center"
            color={theme.palette.primary.main}
            mb={2}
            mt={2}
          >
            <Typography variant={isMobileView ? "h6" : "h4"} textAlign="center">
              Events in den nächsten 3 Wochen
            </Typography>
          </Box>
          <Divider />
          {data?.events?.map((event, index) => (
            <Box key={index}>
              <Box
                key={index}
                display="flex"
                width="100%"
                flexDirection="row"
                sx={{ mb: 4, mt: 4 }}
              >
                <Box width="100px" sx={{ mr: 2 }}>
                  <DateIndicator start={event.start} end={event.end} />
                </Box>
                <Box display="flex" width="100%">
                  <Box width="100%">
                    <Box width="100%">
                      <img
                        style={{
                          float: "right",
                          width: isMobileView ? "100%" : "40%",
                          marginLeft: 32,
                          marginBottom: isMobileView ? 16 : 0,
                        }}
                        src={event.image?.fileUrl}
                      />
                    </Box>
                    <Stack>
                      <Typography variant="h5">{event.titleDe}</Typography>
                      <WhenIndicator start={event.start} end={event.end} />
                      <WhereIndicator eventLocation={event.eventLocation} />
                    </Stack>
                    <Typography
                      component="span"
                      variant={isMobileView ? "body2" : "body1"}
                    >
                      {textFormatter(event.descriptionDe ?? "")}
                    </Typography>
                  </Box>
                </Box>
              </Box>
              {index !== (data.events?.length ?? 0) - 1 && <Divider />}
            </Box>
          ))}
        </CardContent>
      </Card>
    </ElementWrapper>
  );
};

type WhenIndicatorProps = {
  readonly start?: Date;
  readonly end?: Date;
};

const WhenIndicator = (props: WhenIndicatorProps) => {
  if (
    props.start?.getMonth() === props.end?.getMonth() &&
    props.start?.getDate() == props.start?.getDate()
  ) {
    return (
      <Typography>
        Wann: {props.start?.toLocaleDateString("de-DE")}{" "}
        {props.start?.toLocaleTimeString("de-DE", {
          minute: "2-digit",
          hour: "2-digit",
        })}{" "}
        -{" "}
        {props.end?.toLocaleTimeString("de-DE", {
          minute: "2-digit",
          hour: "2-digit",
        })}
      </Typography>
    );
  }

  return (
    <Typography>
      Wann: {props.start?.toLocaleDateString("de-DE")}{" "}
      {props.start?.toLocaleTimeString("de-DE", {
        hour: "2-digit",
        minute: "2-digit",
      })}{" "}
      -{props.end?.toLocaleDateString("de-DE")}{" "}
      {props.end?.toLocaleTimeString("de-DE", {
        hour: "2-digit",
        minute: "2-digit",
      })}
    </Typography>
  );
};

type WhereIndicatorProps = {
  readonly eventLocation?: EventLocationDto;
};

const WhereIndicator = (props: WhereIndicatorProps) => {
  return (
    <Stack direction="row" spacing={1}>
      <Typography>Wo:</Typography>
      <Stack>
        <Typography>{props.eventLocation?.name}</Typography>
        <Typography variant="caption">{props.eventLocation?.street}</Typography>
        <Typography variant="caption">{props.eventLocation?.city}</Typography>
      </Stack>
    </Stack>
  );
};

type DateIndicatorProps = {
  readonly start?: Date;
  readonly end?: Date;
};

const DateIndicator = (props: DateIndicatorProps) => {
  return (
    <Stack alignItems="center">
      <Typography variant="overline" lineHeight={1.2}>
        {props.start?.toLocaleDateString("de-DE", {
          month: "short",
        })}
      </Typography>
      <Typography lineHeight={1.2}>{props.start?.getDate()}</Typography>
      <Typography variant="caption">{props.start?.getFullYear()}</Typography>
    </Stack>
  );
};
