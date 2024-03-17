import { useQuery } from "@tanstack/react-query";
import { eventApi } from "../../../../../infrastructure/api";
import { useMemo } from "react";
import { convertDatesForRecurringEvents } from "../../../../../services/date-time.service";

export const useRecurringEventsQuery = () => {
  const { data, isLoading } = useQuery({
    queryKey: ["all-recurring-events"],
    queryFn: () => eventApi.getAllRecurringEvents(),
    refetchOnWindowFocus: false,
  });

  const convertedData = useMemo(
    () => data?.data.events?.map((e) => convertDatesForRecurringEvents(e)),
    [data]
  );

  return { data: convertedData, isLoading };
};
