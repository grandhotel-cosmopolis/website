import { useQuery } from "@tanstack/react-query";
import { eventApi } from "../../../../infrastructure/api";
import { useMemo } from "react";
import { convertDatesForSingleEvents } from "../../../../services/date-time.service";

export const useUpcomingEventsQuery = () => {
  const { data, isLoading } = useQuery({
    queryKey: ["upcoming-events"],
    queryFn: () => eventApi.getSingleEvents(),
  });

  const convertedData = useMemo(
    () => data?.data.events?.map((e) => convertDatesForSingleEvents(e)),
    [data]
  );

  const events = useMemo(() => {
    return convertedData?.sort((a, b) => {
      const startA = a.exception?.start ?? a.start;
      const startB = b.exception?.start ?? b.start;
      if (!!startA && !startB) {
        return 1;
      }
      if (!startA && !!startB) {
        return -1;
      }
      if (startA !== undefined && startB !== undefined) {
        return startA > startB ? 1 : -1;
      }
      return 0;
    });
  }, [data]);

  return { data: events, isLoading };
};
