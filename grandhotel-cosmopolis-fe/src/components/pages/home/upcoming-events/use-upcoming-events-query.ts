import { useQuery } from "react-query";
import { eventApi } from "../../../../infrastructure/api";
import {
  ListSingleEventDto,
  SingleEventDto,
} from "../../../../infrastructure/generated/openapi/api";
import { useMemo } from "react";

export const useUpcomingEventsQuery = () => {
  const { data, isLoading } = useQuery({
    queryKey: ["upcoming-events"],
    queryFn: () => eventApi.getSingleEvents(),
  });

  const eventData = useMemo(() => {
    const events = data?.data.events
      ?.map(
        (e) =>
          ({
            title_de: e.titleDe,
            title_en: e.titleEn,
            description_de: e.descriptionDe,
            description_en: e.descriptionEn,
            eventLocation: e.eventLocation,
            start: e.start ? new Date(e.start) : undefined,
            end: e.end ? new Date(e.end) : undefined,
            image: e.image,
          } as SingleEventDto)
      )
      .sort(
        (a, b) =>
          (a.start !== undefined ? a.start.getTime() : 0) -
          (b.start !== undefined ? b.start.getTime() : 0)
      );
    const singleEvents: ListSingleEventDto = {
      events: events,
    };
    return events ? singleEvents : undefined;
  }, [data]);

  return { data: eventData, isLoading };
};
