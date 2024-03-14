import { useMutation, useQueryClient } from "react-query";
import { RecurringEventDto } from "../../../../../infrastructure/generated/openapi";
import { eventApi } from "../../../../../infrastructure/api";

export const useRecurringEventMutations = (onSuccess?: () => void) => {
  const queryClient = useQueryClient();

  const createEventMutation = useMutation({
    mutationFn: (newEvent?: RecurringEventDto) =>
      eventApi.createRecurringEvent(
        newEvent?.titleDe,
        newEvent?.titleEn,
        newEvent?.descriptionDe,
        newEvent?.descriptionEn,
        newEvent?.eventLocation?.guid,
        newEvent?.image?.guid,
        newEvent?.startFirstOccurrence,
        newEvent?.endFirstOccurrence,
        newEvent?.endRecurrence ?? undefined,
        newEvent?.recurrence,
        newEvent?.recurrenceMetadata,
        newEvent?.isPublic ?? false
      ),
    onSuccess: () => {
      queryClient.invalidateQueries("all-recurring-events");
      onSuccess && onSuccess();
    },
  });

  const updateEventMutation = useMutation({
    mutationFn: (editEvent?: RecurringEventDto) =>
      eventApi.updateRecurringEvent(
        editEvent?.guid ?? "",
        editEvent?.titleDe,
        editEvent?.titleEn,
        editEvent?.descriptionDe,
        editEvent?.descriptionEn,
        editEvent?.eventLocation?.guid,
        editEvent?.image?.guid,
        editEvent?.startFirstOccurrence,
        editEvent?.endFirstOccurrence,
        editEvent?.endRecurrence ?? undefined,
        editEvent?.recurrence,
        editEvent?.recurrenceMetadata,
        editEvent?.isPublic ?? false
      ),
    onSuccess: () => {
      queryClient.invalidateQueries("all-recurring-events");
      onSuccess && onSuccess();
    },
  });

  const publishEventMutation = useMutation({
    mutationFn: (event?: RecurringEventDto) =>
      eventApi.publishRecurringEvent(event?.guid ?? ""),
    onSuccess: () => {
      queryClient.invalidateQueries("all-recurring-events");
      onSuccess && onSuccess();
    },
  });

  const unpublishEventMutation = useMutation({
    mutationFn: (event?: RecurringEventDto) =>
      eventApi.unpublishRecurringEvent(event?.guid ?? ""),
    onSuccess: () => {
      queryClient.invalidateQueries("all-recurring-events");
      onSuccess && onSuccess();
    },
  });

  const deleteEventMutation = useMutation({
    mutationFn: (event?: RecurringEventDto) =>
      eventApi.deleteRecurringEvent(event?.guid ?? ""),
    onSuccess: () => {
      queryClient.invalidateQueries("all-recurring-events");
      onSuccess && onSuccess();
    },
  });

  return {
    createEventMutation,
    updateEventMutation,
    publishEventMutation,
    unpublishEventMutation,
    deleteEventMutation,
  };
};
