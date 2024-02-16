import { Box, Button, Typography } from "@mui/material";
import { CreateEventDialog } from "./create-event-dialog";
import { useEffect, useState } from "react";
import { SingleEventDto } from "../../../../infrastructure/generated/openapi";
import { eventApi } from "../../../../infrastructure/api";
import { Table } from "../../../shared/table/table";
import { TableColumn } from "../../../shared/table/table-head";

const bla: TableColumn<SingleEventDto> = [
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
    renderCell: (item: SingleEventDto) => (
      <Typography>{item.isPublic ? "PUBLIC" : "PRIVATE"}</Typography>
    ),
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
