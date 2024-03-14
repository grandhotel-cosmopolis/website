import { Box } from "@mui/material";
import { useState } from "react";
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
      overflow="hidden"
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
    </Box>
  );
};
