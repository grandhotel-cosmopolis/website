import { Box, Typography } from "@mui/material";
import { ReactElement, useState } from "react";
import { SingleEventDto } from "../../../../../infrastructure/generated/openapi";
import { Table } from "../../../../shared/table/table";
import { SingleEventDetailsDialog } from "../single-event-details-dialog/single-event-details-dialog";
import { useSingleEventsQuery } from "./use-single-events-query";
import { useSingleEventColumns } from "./use-single-event-columns";

export const SingleEventsTab = () => {
  const [selectedEvent, setSelectedEvent] = useState<SingleEventDto>();

  const { data } = useSingleEventsQuery();
  const columns = useSingleEventColumns();

  return (
    <Box
      height="100%"
      display="flex"
      flexDirection="column"
      overflow={"hidden"}
      sx={{ p: 1 }}
    >
      <SingleEventDetailsDialog
        singleEvent={selectedEvent}
        open={!!selectedEvent}
        closeDialog={() => setSelectedEvent(undefined)}
        mode="Update"
      />
      <Table<SingleEventDto>
        columns={columns}
        items={data ?? []}
        onItemClick={(item) => setSelectedEvent(item)}
      />
      {/* <CreateEventDialog
        open={createEvent}
        close={() => setCreateEvent(false)}
      /> */}
      {/* <Button variant="contained" onClick={() => setCre)ateEvent(true}>
        Create new Event
      </Button> */}
    </Box>
  );
};

export const renderDateCell = (start?: Date, end?: Date): ReactElement => {
  if (!!start && !end) {
    return <SingleDate date={start} />;
  }
  if (!!end && !start) {
    return <SingleDate date={end} />;
  }
  if (!!start && !!end) {
    return <DoubleDate start={start} end={end} />;
  }
  return <></>;
};

type SingleDateProps = {
  readonly date: Date;
};

export const SingleDate = (props: SingleDateProps) => {
  return <Typography>{props.date.toLocaleDateString("de-DE")}</Typography>;
};

type DoubleDateProps = {
  readonly start: Date;
  readonly end: Date;
};

export const DoubleDate = (props: DoubleDateProps) => {
  if (props.start.getDate() === props.end.getDate()) {
    return (
      <Typography>
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
      </Typography>
    );
  }
  return (
    <Typography>
      {props.start.toLocaleString("de-DE", {
        dateStyle: "short",
        timeStyle: "short",
      })}{" "}
      -{" "}
      {props.end.toLocaleString("de-DE", {
        dateStyle: "short",
        timeStyle: "short",
      })}
    </Typography>
  );
};
