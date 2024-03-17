import { useQuery } from "@tanstack/react-query";
import { eventApi } from "../../../../../infrastructure/api";
import { RecurringEventDto } from "../../../../../infrastructure/generated/openapi";
import { useMemo } from "react";
import { convertDatesForSingleEvents } from "../../../../../services/date-time.service";

export const useSingleEventsForRecurringEventQuery = (
  event?: RecurringEventDto
) => {
  const { data, isLoading } = useQuery({
    queryKey: ["single-events", event?.guid ?? ""],
    queryFn: () =>
      eventApi.getSingleEventsByRecurringEventGuid(event?.guid ?? ""),
    refetchOnWindowFocus: false,
  });

  const convertedData = useMemo(
    () => data?.data.events?.map((e) => convertDatesForSingleEvents(e)),
    [data]
  );

  return { data: convertedData, isLoading };
};
