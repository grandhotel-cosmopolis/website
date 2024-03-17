import {
  RecurringEventDto,
  SingleEventDto,
} from "../../../../../infrastructure/generated/openapi";
import { useSingleEventsForRecurringEventQuery } from "./use-single-events-for-recurring-event-query";
import { Table } from "../../../../shared/table/table";
import { useSingleEventListColumns } from "./useSingleEventListColumns";
import { useState } from "react";
import { SingleEventDetailsDialog } from "../single-event-details-dialog/single-event-details-dialog";
import { useQueryClient } from "@tanstack/react-query";

type RecurringEventListSingleEventsContentProps = {
  readonly recurringEvent?: RecurringEventDto;
};
export const RecurringEventListSingleEventsContent = (
  props: RecurringEventListSingleEventsContentProps
) => {
  const [selectedEvent, setSelectedEvent] = useState<SingleEventDto>();
  const { data } = useSingleEventsForRecurringEventQuery(props.recurringEvent);
  const columns = useSingleEventListColumns();
  const queryClient = useQueryClient();

  return (
    <>
      <Table<SingleEventDto>
        columns={columns}
        items={data ?? []}
        onItemClick={(item) => setSelectedEvent(item)}
      />
      <SingleEventDetailsDialog
        singleEvent={selectedEvent}
        open={!!selectedEvent}
        closeDialog={() => {
          setSelectedEvent(undefined);
          queryClient.invalidateQueries({
            queryKey: ["single-events", props.recurringEvent?.guid ?? ""],
          });
        }}
        mode="Update"
      />
    </>
  );
};
