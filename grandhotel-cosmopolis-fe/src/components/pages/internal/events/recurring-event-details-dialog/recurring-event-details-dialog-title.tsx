import { DialogTitle, Box, Typography, Stack } from "@mui/material";
import { EditButton } from "../../../../shared/buttons/edit-button";
import { Dispatch, SetStateAction } from "react";
import { PublishButton } from "../../../../shared/buttons/publish-button";
import { RecurringEventDto } from "../../../../../infrastructure/generated/openapi";
import { DeleteButton } from "../../../../shared/buttons/delete-button";
import { useRecurringEventMutations } from "./use-recurring-event-mutations";
import { Mode } from "./recurrong-event-details-dialog";
import { ListButton } from "../../../../shared/buttons/list-button";

type RecurringEventDetailsDialogTitleProps = {
  readonly listMode: boolean;
  readonly setListMode: Dispatch<SetStateAction<boolean>>;
  readonly editMode: boolean;
  readonly setEditMode: Dispatch<SetStateAction<boolean>>;
  readonly recurringEvent?: RecurringEventDto;
  readonly setRecurringEvent: Dispatch<
    SetStateAction<RecurringEventDto | undefined>
  >;
  readonly closeDialog: () => void;
  readonly mode: Mode;
};

export const RecurringEventDetailsDialogTitle = (
  props: RecurringEventDetailsDialogTitleProps
) => {
  const { publishEventMutation, unpublishEventMutation } =
    useRecurringEventMutations();

  const { deleteEventMutation } = useRecurringEventMutations(props.closeDialog);

  return (
    <DialogTitle>
      <Box display="flex" justifyContent="space-between">
        <Typography>Event details</Typography>
        <Stack direction="row" spacing={2}>
          {props.mode === "Update" && (
            <DeleteButton
              onClick={() => deleteEventMutation.mutate(props.recurringEvent)}
            />
          )}
          <PublishButton
            published={!!props.recurringEvent?.isPublic}
            onClick={() =>
              props.setRecurringEvent((curr) => {
                if (props.mode !== "Create") {
                  if (curr?.isPublic) {
                    unpublishEventMutation.mutate(props.recurringEvent);
                  } else {
                    publishEventMutation.mutate(props.recurringEvent);
                  }
                }
                return { ...curr, isPublic: !curr?.isPublic };
              })
            }
          />
          {props.mode === "Update" && (
            <ListButton
              active={props.listMode}
              onClick={() =>
                props.setListMode((curr) => {
                  if (!curr) {
                    props.setEditMode(false);
                  }
                  return !curr;
                })
              }
            />
          )}
          <EditButton
            active={props.editMode}
            onClick={() =>
              props.setEditMode((curr) => {
                if (!curr) {
                  props.setListMode(false);
                }
                return !curr;
              })
            }
          />
        </Stack>
      </Box>
    </DialogTitle>
  );
};
