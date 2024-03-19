import { useQuery } from "@tanstack/react-query";
import { eventApi } from "../../../../../infrastructure/api";

export const useRecurringEventsQuery = () => {
  const { data, isLoading } = useQuery({
    queryKey: ["all-recurring-events"],
    queryFn: () => eventApi.getAllRecurringEvents(),
    refetchOnWindowFocus: false,
  });

  return { data: data?.data.events, isLoading };
};
