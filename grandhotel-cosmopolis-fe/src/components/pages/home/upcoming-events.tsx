import {
  Box,
  Card,
  CardContent,
  Divider,
  Typography,
  useTheme,
} from "@mui/material";
import { ElementWrapper } from "../../shared/element-wrapper";
import { useUpcomingEventsQuery } from "./upcoming-events/use-upcoming-events-query";
import { useIsMobileView } from "../../hooks/screen-sizes/use-is-mobile-view";
import { SingleEventView } from "../../shared/events/single-events/single-event-view";

export const UpcomingEvents = () => {
  const { data } = useUpcomingEventsQuery();
  const isMobileView = useIsMobileView();
  const theme = useTheme();

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
          {data?.map((event, index) => (
            <Box key={index}>
              <SingleEventView
                singleEvent={event}
                isMobileView={isMobileView}
              />
              {index !== (data?.length ?? 0) - 1 && <Divider />}
            </Box>
          ))}
        </CardContent>
      </Card>
    </ElementWrapper>
  );
};
