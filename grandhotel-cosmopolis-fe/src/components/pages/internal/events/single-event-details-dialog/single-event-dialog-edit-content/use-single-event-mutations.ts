import { useMutation, useQueryClient } from "react-query";
import { eventApi } from "../../../../../../infrastructure/api";
import { SingleEventDto } from "../../../../../../infrastructure/generated/openapi";

export const useSingleEventMutations = (onSuccess?: () => void) => {
  const queryClient = useQueryClient();

  const addEventMutation = useMutation({
    mutationFn: (newEvent?: SingleEventDto) =>
      eventApi.addSingleEvent(
        newEvent?.titleDe,
        newEvent?.titleEn,
        newEvent?.descriptionDe,
        newEvent?.descriptionEn,
        newEvent?.start,
        newEvent?.end,
        newEvent?.isPublic ?? false,
        newEvent?.eventLocation?.guid,
        newEvent?.image?.guid
      ),
    onSuccess: () => {
      queryClient.invalidateQueries("all-single-events");
      onSuccess && onSuccess();
    },
  });

  const updateEventMutation = useMutation({
    mutationFn: (editEvent?: SingleEventDto) =>
      eventApi.updateSingleEvent(
        editEvent?.guid ?? "",
        editEvent?.titleDe,
        editEvent?.titleEn,
        editEvent?.descriptionDe,
        editEvent?.descriptionEn,
        editEvent?.start,
        editEvent?.end,
        editEvent?.isPublic ?? false,
        editEvent?.eventLocation?.guid,
        editEvent?.image?.guid
      ),
    onSuccess: () => {
      queryClient.invalidateQueries("all-single-events");
      onSuccess && onSuccess();
    },
  });

  const publishEventMutation = useMutation({
    mutationFn: (event?: SingleEventDto) =>
      eventApi.publishSingleEvent(event?.guid ?? ""),
    onSuccess: () => {
      queryClient.invalidateQueries("all-single-events");
      onSuccess && onSuccess();
    },
  });

  const unpublishEventMutation = useMutation({
    mutationFn: (event?: SingleEventDto) =>
      eventApi.unpublishSingleEvent(event?.guid ?? ""),
    onSuccess: () => {
      queryClient.invalidateQueries("all-single-events");
      onSuccess && onSuccess();
    },
  });

  const deleteEventMutataion = useMutation({
    mutationFn: (event?: SingleEventDto) =>
      eventApi.deleteSingleEvent(event?.guid ?? ""),
    onSuccess: () => {
      queryClient.invalidateQueries("all-single-events");
      onSuccess && onSuccess();
    },
  });

  const cancelEventMutation = useMutation({
    mutationFn: (event?: SingleEventDto) => eventApi.cancel(event?.guid ?? ""),
    onSuccess: () => {
      queryClient.invalidateQueries("all-single-events");
      onSuccess && onSuccess();
    },
  });

  const uncancelEventMutation = useMutation({
    mutationFn: (event?: SingleEventDto) =>
      eventApi.uncancel(event?.guid ?? ""),
    onSuccess: () => {
      queryClient.invalidateQueries("all-single-events");
      onSuccess && onSuccess();
    },
  });

  const createOrUpdateExceptionMutation = useMutation({
    mutationFn: (event?: SingleEventDto) =>
      eventApi.createOrUpdateException(
        event?.guid ?? "",
        event?.exception?.start ?? undefined,
        event?.exception?.end ?? undefined,
        event?.exception?.eventLocation?.guid,
        event?.exception?.cancelled ?? undefined
      ),
    onSuccess: () => {
      queryClient.invalidateQueries("all-single-events");
      onSuccess && onSuccess();
    },
  });

  return {
    addEventMutation,
    updateEventMutation,
    publishEventMutation,
    unpublishEventMutation,
    deleteEventMutataion,
    cancelEventMutation,
    uncancelEventMutation,
    createOrUpdateExceptionMutation,
  };
};
