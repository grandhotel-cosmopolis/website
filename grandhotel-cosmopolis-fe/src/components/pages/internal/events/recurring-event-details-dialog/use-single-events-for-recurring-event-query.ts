import { useQuery } from "react-query";
import { eventApi } from "../../../../../infrastructure/api";
import { RecurringEventDto } from "../../../../../infrastructure/generated/openapi";

export const useSingleEventsForRecurringEventQuery = (
  event?: RecurringEventDto
) => {
  const { data, isLoading } = useQuery({
    queryKey: ["single-events", event?.guid ?? ""],
    queryFn: () =>
      eventApi.getSingleEventsByRecurringEventGuid(event?.guid ?? ""),
    refetchOnWindowFocus: false,
  });

  return { data: data?.data.events, isLoading };
};
