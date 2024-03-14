import { Dialog, DialogContent, DialogActions, Button } from "@mui/material";
import { useState, useEffect } from "react";
import { RecurringEventDto } from "../../../../../infrastructure/generated/openapi";
import { useRecurringEventMutations } from "./use-recurring-event-mutations";
import { RecurringEventDetailsDialogTitle } from "./recurring-event-details-dialog-title";
import { RecurringEventDialogEditContent } from "./recurring-event-dialog-edit-content";
import { RecurringEventListSingleEventsContent } from "./recurring-event-list-single-events-content";

export type Mode = "Create" | "Update";

type RecurringEventDetailsDialogProps = {
  readonly open: boolean;
  readonly recurringEvent?: RecurringEventDto;
  readonly closeDialog: () => void;
  readonly mode: Mode;
};

export const RecurringEventDetailsDialog = (
  props: RecurringEventDetailsDialogProps
) => {
  const [currentRecurringEvent, setCurrentRecurringEvent] = useState(
    props.recurringEvent
  );
  const [editMode, setEditMode] = useState(props.mode === "Create");
  const [listMode, setListMode] = useState(false);
  const [saveEnabled, setSaveEnabled] = useState(false);

  const { createEventMutation, updateEventMutation } =
    useRecurringEventMutations(() => {
      setEditMode(false);
      setListMode(false);
      setCurrentRecurringEvent(undefined);
      setSaveEnabled(false);
      props.closeDialog();
    });

  useEffect(() => {
    setCurrentRecurringEvent(props.recurringEvent);
  }, [props.recurringEvent]);

  useEffect(() => {
    if (props.open && props.mode === "Create") {
      setEditMode(true);
    }
  }, [props.open, props.mode]);

  useEffect(() => {
    if (
      !!currentRecurringEvent?.titleDe &&
      !!currentRecurringEvent?.titleEn &&
      !!currentRecurringEvent?.descriptionDe &&
      !!currentRecurringEvent?.descriptionEn &&
      !!currentRecurringEvent?.startFirstOccurrence &&
      !!currentRecurringEvent?.endFirstOccurrence &&
      !!currentRecurringEvent?.recurrence &&
      !!currentRecurringEvent?.recurrenceMetadata &&
      !!currentRecurringEvent?.eventLocation?.guid &&
      !!currentRecurringEvent?.image?.guid
    ) {
      setSaveEnabled(true);
    } else {
      setSaveEnabled(false);
    }
  }, [currentRecurringEvent]);

  const handleSave = () => {
    if (saveEnabled) {
      if (props.recurringEvent?.guid) {
        updateEventMutation.mutate(currentRecurringEvent);
      } else {
        createEventMutation.mutate(currentRecurringEvent);
      }
    }
  };

  return (
    <Dialog open={props.open} fullWidth maxWidth="xl">
      <RecurringEventDetailsDialogTitle
        listMode={listMode}
        setListMode={setListMode}
        editMode={editMode}
        setEditMode={setEditMode}
        recurringEvent={currentRecurringEvent}
        setRecurringEvent={setCurrentRecurringEvent}
        closeDialog={props.closeDialog}
        mode={props.mode}
      />
      <DialogContent
        sx={{ height: "80vh", display: "flex", flexDirection: "column" }}
      >
        {listMode ? (
          <RecurringEventListSingleEventsContent
            recurringEvent={currentRecurringEvent}
          />
        ) : (
          <RecurringEventDialogEditContent
            isReadOnly={!editMode}
            closeDialog={() => {
              setEditMode(false);
              setListMode(false);
              props.closeDialog();
            }}
            recurringEvent={currentRecurringEvent}
            setRecurringEvent={setCurrentRecurringEvent}
          />
        )}
      </DialogContent>
      <DialogActions>
        <Button
          variant="outlined"
          onClick={() => {
            props.mode !== "Create" && setEditMode(false);
            setListMode(false);
            setCurrentRecurringEvent(undefined);
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
