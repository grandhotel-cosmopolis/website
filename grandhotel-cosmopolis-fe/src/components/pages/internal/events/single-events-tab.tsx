import { Box, Button, Stack, Typography } from "@mui/material";
import { CreateEventDialog } from "./create-event-dialog";
import { ReactElement, useEffect, useState } from "react";
import { SingleEventDto } from "../../../../infrastructure/generated/openapi";
import { eventApi } from "../../../../infrastructure/api";
import { Table, TableColumn } from "../../../shared/table/table";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import CloseIcon from "@mui/icons-material/Close";
import CheckIcon from "@mui/icons-material/Check";

const bla: TableColumn<SingleEventDto>[] = [
  {
    id: "start",
    label: (
      <Stack direction="row">
        <CalendarTodayIcon />
        <Typography ml={2}>Date</Typography>
      </Stack>
    ),
    renderCell: (item: SingleEventDto) => renderDateCell(item.start, item.end),
  },
  {
    id: "titleDe",
    label: "titleDe",
    renderCell: (item: SingleEventDto) => (
      <Typography>{item.titleDe}</Typography>
    ),
  },
  {
    id: "titleEn",
    label: "titleEn",
    renderCell: (item: SingleEventDto) => (
      <Typography>{item.titleEn}</Typography>
    ),
  },
  {
    id: "isPublic",
    label: "isPublic",
    renderCell: (item: SingleEventDto) =>
      item.isPublic ? <CheckIcon /> : <CloseIcon />,
  },
];

export const SingleEventsTab = () => {
  const [createEvent, setCreateEvent] = useState(false);
  const [singleEvents, setSingleEVents] = useState<SingleEventDto[]>([]);

  useEffect(() => {
    eventApi
      .getAllSingleEvents()
      .then((r) => setSingleEVents(r.data.events ?? []));
  }, []);

  return (
    <Box
      height="100%"
      display="flex"
      flexDirection="column"
      overflow={"hidden"}
      sx={{ p: 1 }}
    >
      <Table<SingleEventDto> columns={bla} items={singleEvents} />
      <CreateEventDialog
        open={createEvent}
        close={() => setCreateEvent(false)}
      />
      <Button variant="contained" onClick={() => setCreateEvent(true)}>
        Create new Event
      </Button>
    </Box>
  );
};

const renderDateCell = (start?: Date, end?: Date): ReactElement => {
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

const SingleDate = (props: SingleDateProps) => {
  return <Typography>{props.date.toLocaleDateString("de-DE")}</Typography>;
};

type DoubleDateProps = {
  readonly start: Date;
  readonly end: Date;
};

const DoubleDate = (props: DoubleDateProps) => {
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
