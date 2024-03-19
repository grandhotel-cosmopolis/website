import { useMemo } from "react";
import { eventApi } from "../../../infrastructure/api";
import { useQuery } from "@tanstack/react-query";

export const useEventsQuery = (from: Date, to: Date) => {
  const { data, isLoading } = useQuery({
    queryKey: ["public-events", from.toISOString(), to.toISOString()],
    queryFn: () => eventApi.getSingleEvents(from, to),
  });

  const events = useMemo(() => {
    return data?.data.events?.sort((a, b) => {
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
