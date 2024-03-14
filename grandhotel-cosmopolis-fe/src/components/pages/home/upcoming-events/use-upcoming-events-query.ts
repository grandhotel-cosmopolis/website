import { useQuery } from "react-query";
import { eventApi } from "../../../../infrastructure/api";
import { useMemo } from "react";

export const useUpcomingEventsQuery = () => {
  const { data, isLoading } = useQuery({
    queryKey: ["upcoming-events"],
    queryFn: () => eventApi.getSingleEvents(),
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
