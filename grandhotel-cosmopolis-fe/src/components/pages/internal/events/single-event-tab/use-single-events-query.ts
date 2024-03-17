import { useQuery } from "@tanstack/react-query";
import { eventApi } from "../../../../../infrastructure/api";
import { useMemo } from "react";
import { convertDatesForSingleEvents } from "../../../../../services/date-time.service";

export const useSingleEventsQuery = () => {
  const { data, isLoading } = useQuery({
    queryKey: ["all-single-events"],
    queryFn: () => eventApi.getAllSingleEvents(),
    refetchOnWindowFocus: false,
  });

  const convertedData = useMemo(
    () => data?.data.events?.map((e) => convertDatesForSingleEvents(e)),
    [data]
  );

  return { data: convertedData, isLoading };
};
