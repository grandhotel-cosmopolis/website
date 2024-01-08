import { Box, Card, CardContent, Stack, Typography } from "@mui/material";
import { ElementWrapper } from "../../shared/element-wrapper";
import { useUpcomingEventsQuery } from "./upcoming-events/use-upcoming-events-query";
import { EventLocationDto } from "../../../infrastructure/generated/openapi";

export const UpcomingEvents = () => {
  const { data } = useUpcomingEventsQuery();

  return (
    <ElementWrapper>
      <Card sx={{ width: "100%" }}>
        <CardContent>
          {data?.events?.map((event, index) => (
            <Box
              key={index}
              display="flex"
              width="100%"
              flexDirection="row"
              sx={{ mb: 4 }}
            >
              <Box width="100px" sx={{ mr: 2 }}>
                <DateIndicator start={event.start} end={event.end} />
              </Box>
              <Box flexGrow={1}>
                <Stack>
                  <Typography variant="h5">{event.title_de}</Typography>
                  <WhenIndicator start={event.start} end={event.end} />
                  <WhereIndicator eventLocation={event.eventLocation} />
                  <Typography>{event.description_de}</Typography>
                </Stack>
              </Box>
              <Box width="300px">3</Box>
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
