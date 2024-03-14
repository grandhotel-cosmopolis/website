import { Box } from "@mui/material";
import { useRecurringEventsQuery } from "./use-recurring-event-query";
import { useRecurringEventColumns } from "./use-recurring-event-columns";
import { Table } from "../../../../shared/table/table";
import { RecurringEventDto } from "../../../../../infrastructure/generated/openapi";
import { RecurringEventDetailsDialog } from "../recurring-event-details-dialog/recurrong-event-details-dialog";
import { useState } from "react";

export const RecurringEventsTab = () => {
  const [selectedEvent, setSelectedEvent] = useState<RecurringEventDto>();
  const { data } = useRecurringEventsQuery();
  const columns = useRecurringEventColumns();

  return (
    <Box
      height="100%"
      display="flex"
      flexDirection="column"
      overflow="hidden"
      sx={{ p: 1 }}
    >
      <Table<RecurringEventDto>
        columns={columns}
        items={data ?? []}
        onItemClick={(item) => setSelectedEvent(item)}
      />
      <RecurringEventDetailsDialog
        recurringEvent={selectedEvent}
        open={!!selectedEvent}
        closeDialog={() => setSelectedEvent(undefined)}
        mode="Update"
      />
    </Box>
  );
};
