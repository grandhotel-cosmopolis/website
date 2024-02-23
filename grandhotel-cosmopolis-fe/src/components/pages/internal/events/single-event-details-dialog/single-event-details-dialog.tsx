import { Button, Dialog, DialogActions, DialogContent } from "@mui/material";
import { SingleEventDialogEditContent } from "./single-event-dialog-edit-content/single-event-dialog-edit-content";
import { SingleEventDto } from "../../../../../infrastructure/generated/openapi";
import { useEffect, useState } from "react";
import { SingleEventDetailsDialogTitle } from "./single-event-details-dialog-title";
import { SingleEventDialogPreviewContent } from "./single-event-dialog-preview.-content";
import { useSingleEventMutations } from "./single-event-dialog-edit-content/use-single-event-mutations";

export type Mode = "Create" | "Update";

type SingleEventDetailsDialogProps = {
  readonly open: boolean;
  readonly singleEvent?: SingleEventDto;
  readonly closeDialog: () => void;
  readonly mode: Mode;
};

export const SingleEventDetailsDialog = (
  props: SingleEventDetailsDialogProps
) => {
  const [currentSingleEvent, setCurrentSingleEvent] = useState(
    props.singleEvent
  );
  const [editMode, setEditMode] = useState(props.mode === "Create");
  const [preview, setPreview] = useState(false);
  const [saveEnabled, setSaveEnabled] = useState(false);

  const { addEventMutation, updateEventMutation } = useSingleEventMutations(
    () => {
      setEditMode(false);
      setPreview(false);
      setCurrentSingleEvent(undefined);
      setSaveEnabled(false);
      props.closeDialog();
    }
  );

  useEffect(() => {
    setCurrentSingleEvent(props.singleEvent);
  }, [props.singleEvent]);

  useEffect(() => {
    if (props.open && props.mode === "Create") {
      setEditMode(true);
    }
  }, [props.open, props.mode]);

  useEffect(() => {
    if (
      !!currentSingleEvent?.titleDe &&
      !!currentSingleEvent?.titleEn &&
      !!currentSingleEvent?.descriptionDe &&
      !!currentSingleEvent?.descriptionEn &&
      !!currentSingleEvent?.start &&
      !!currentSingleEvent?.end &&
      !!currentSingleEvent?.eventLocation?.guid &&
      !!currentSingleEvent?.image?.guid
    ) {
      setSaveEnabled(true);
    } else {
      setSaveEnabled(false);
    }
  }, [currentSingleEvent]);

  const handleSave = () => {
    if (saveEnabled) {
      if (props.singleEvent?.guid) {
        updateEventMutation.mutate(currentSingleEvent);
      } else {
        addEventMutation.mutate(currentSingleEvent);
      }
    }
  };

  return (
    <Dialog open={props.open} fullWidth maxWidth="xl">
      <SingleEventDetailsDialogTitle
        preview={preview}
        setPreview={setPreview}
        editMode={editMode}
        setEditMode={setEditMode}
        singleEvent={currentSingleEvent}
        setSingleEvent={setCurrentSingleEvent}
        closeDialog={props.closeDialog}
        mode={props.mode}
      />
      <DialogContent
        sx={{ height: "80vh", display: "flex", flexDirection: "column" }}
      >
        {preview ? (
          <SingleEventDialogPreviewContent singleEvent={currentSingleEvent} />
        ) : (
          <SingleEventDialogEditContent
            isReadOnly={!editMode}
            closeDialog={() => {
              setEditMode(false);
              setPreview(false);
              props.closeDialog();
            }}
            singleEvent={currentSingleEvent}
            setSingleEvent={setCurrentSingleEvent}
          />
        )}
      </DialogContent>
      <DialogActions>
        <Button
          variant="outlined"
          onClick={() => {
            props.mode !== "Create" && setEditMode(false);
            setPreview(false);
            setCurrentSingleEvent(undefined);
            setSaveEnabled(false);
            props.closeDialog();
          }}
        >
          Close
        </Button>
        <Button disabled={!saveEnabled} variant="outlined" onClick={handleSave}>
          Save
        </Button>
      </DialogActions>
    </Dialog>
  );
};
