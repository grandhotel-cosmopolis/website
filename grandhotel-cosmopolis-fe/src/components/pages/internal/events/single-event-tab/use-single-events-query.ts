import { useQuery } from "@tanstack/react-query";
import { eventApi } from "../../../../../infrastructure/api";

export const useSingleEventsQuery = () => {
  const { data, isLoading } = useQuery({
    queryKey: ["all-single-events"],
    queryFn: () => eventApi.getAllSingleEvents(),
    refetchOnWindowFocus: false,
  });

  return { data: data?.data.events, isLoading };
};
