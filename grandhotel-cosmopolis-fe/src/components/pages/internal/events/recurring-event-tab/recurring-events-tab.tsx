import { Box } from "@mui/material";
import { useRecurringEventsQuery } from "./use-recurring-event-query";
import { useRecurringEventColumns } from "./use-recurring-event-columns";
import { Table } from "../../../../shared/table/table";
import { RecurringEventDto } from "../../../../../infrastructure/generated/openapi";

export const RecurringEventsTab = () => {
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
        onItemClick={() => console.log("hello")}
      />
    </Box>
  );
};
