import { DialogTitle, Box, Typography, Stack } from "@mui/material";
import { EditButton } from "../../../../shared/buttons/edit-button";
import { PreviewButton } from "../../../../shared/buttons/preview-button";
import { Dispatch, SetStateAction } from "react";
import { PublishButton } from "../../../../shared/buttons/publish-button";
import { SingleEventDto } from "../../../../../infrastructure/generated/openapi";
import { useSingleEventMutations } from "./single-event-dialog-edit-content/use-single-event-mutations";
import { DeleteButton } from "../../../../shared/buttons/delete-button";
import { Mode } from "./single-event-details-dialog";

type SingleEventDetailsDialogTitleProps = {
  readonly preview: boolean;
  readonly setPreview: Dispatch<SetStateAction<boolean>>;
  readonly editMode: boolean;
  readonly setEditMode: Dispatch<SetStateAction<boolean>>;
  readonly singleEvent?: SingleEventDto;
  readonly setSingleEvent: Dispatch<SetStateAction<SingleEventDto | undefined>>;
  readonly closeDialog: () => void;
  readonly mode: Mode;
};

export const SingleEventDetailsDialogTitle = (
  props: SingleEventDetailsDialogTitleProps
) => {
  const { publishEventMutation, unpublishEventMutation } =
    useSingleEventMutations();

  const { deleteEventMutataion } = useSingleEventMutations(props.closeDialog);

  return (
    <DialogTitle>
      <Box display="flex" justifyContent="space-between">
        <Typography>Event details</Typography>
        <Stack direction="row" spacing={2}>
          <DeleteButton
            onClick={() => deleteEventMutataion.mutate(props.singleEvent)}
          />
          <PublishButton
            published={!!props.singleEvent?.isPublic}
            onClick={() =>
              props.setSingleEvent((curr) => {
                if (props.mode !== "Create") {
                  if (curr?.isPublic) {
                    unpublishEventMutation.mutate(props.singleEvent);
                  } else {
                    publishEventMutation.mutate(props.singleEvent);
                  }
                }
                return { ...curr, isPublic: !curr?.isPublic };
              })
            }
          />
          <PreviewButton
            active={props.preview}
            onClick={() =>
              props.setPreview((curr) => {
                if (!curr) {
                  props.setEditMode(false);
                }
                return !curr;
              })
            }
          />
          <EditButton
            active={props.editMode}
            onClick={() =>
              props.setEditMode((curr) => {
                if (!curr) {
                  props.setPreview(false);
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
