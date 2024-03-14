import {
  Dialog,
  DialogContent,
  Stack,
  DialogActions,
  Button,
  Typography,
} from "@mui/material";
import { DatePicker, DateTimePicker, TimePicker } from "@mui/x-date-pickers";
import { useState } from "react";
import { Recurrence } from "../../../../../../infrastructure/generated/openapi";
import { RecurrenceControl } from "./reccurrence-control";

type EditRecurrenceDialogProps = {
  readonly open: boolean;
  readonly startFirstOccurrence?: Date;
  readonly endFirstOccurrence?: Date;
  readonly endRecurrence?: Date;
  readonly recurrence?: Recurrence;
  readonly recurrenceMetadata?: number;
  readonly setStartFirstOccurrence: (_: Date) => void;
  readonly setEndFirstOccurrence: (_: Date) => void;
  readonly setEndRecurrence: (_: Date) => void;
  readonly setRecurrence: (_: Recurrence) => void;
  readonly setRecurrenceMetadata: (_?: number) => void;
  readonly close: () => void;
};

export const EditRecurrenceDialog = (props: EditRecurrenceDialogProps) => {
  const [dateFistOccurrence, setDateFirstOccurrence] = useState(
    props.startFirstOccurrence
  );
  const [startFirstOccurrence, setStartFirstOccurrence] = useState(
    props.startFirstOccurrence
  );
  const [endFirstOccurrence, setEndFirstOccurrence] = useState(
    props.endFirstOccurrence
  );

  const handleStartFirstOccurrenceChange = (newValue: Date | null) => {
    if (!!newValue) {
      setStartFirstOccurrence(newValue);
      if (!!dateFistOccurrence) {
        props.setStartFirstOccurrence(
          new Date(
            dateFistOccurrence.getFullYear(),
            dateFistOccurrence.getMonth(),
            dateFistOccurrence.getDate(),
            newValue.getHours(),
            newValue.getMinutes()
          )
        );
      }
    }
  };

  const handleEndFirstOccurrenceChange = (newValue: Date | null) => {
    if (!!newValue) {
      setEndFirstOccurrence(newValue);
      if (!!dateFistOccurrence) {
        props.setEndFirstOccurrence(
          new Date(
            dateFistOccurrence.getFullYear(),
            dateFistOccurrence.getMonth(),
            dateFistOccurrence.getDate(),
            newValue.getHours(),
            newValue.getMinutes()
          )
        );
      }
    }
  };

  const handleDateFirstOccurrenceChange = (newValue: Date | null) => {
    if (!!newValue) {
      setDateFirstOccurrence(newValue);
      if (!!startFirstOccurrence) {
        props.setStartFirstOccurrence(
          new Date(
            newValue.getFullYear(),
            newValue.getMonth(),
            newValue.getDate(),
            startFirstOccurrence.getHours(),
            startFirstOccurrence.getMinutes()
          )
        );
      }
      if (!!endFirstOccurrence) {
        props.setEndFirstOccurrence(
          new Date(
            newValue.getFullYear(),
            newValue.getMonth(),
            newValue.getDate(),
            endFirstOccurrence.getHours(),
            endFirstOccurrence.getMinutes()
          )
        );
      }
    }
  };

  return (
    <Dialog open={props.open}>
      <DialogContent>
        <Stack spacing={2}>
          <Typography>First Occurrence</Typography>
          <Stack spacing={2} px={2}>
            <DatePicker
              label="Day first occurrence"
              onChange={(newValue: Date | null) =>
                handleDateFirstOccurrenceChange(newValue)
              }
              defaultValue={props.startFirstOccurrence}
            />
            <TimePicker
              label="Start first occurrence"
              onChange={(newVal: Date | null) =>
                handleStartFirstOccurrenceChange(newVal)
              }
              defaultValue={props.startFirstOccurrence}
            />
            <TimePicker
              label="End first occurrence"
              onChange={(newVal: Date | null) =>
                handleEndFirstOccurrenceChange(newVal)
              }
              defaultValue={props.endFirstOccurrence}
            />
          </Stack>
          <RecurrenceControl
            recurrence={props.recurrence}
            setRecurrence={props.setRecurrence}
            recurrenceMetadata={props.recurrenceMetadata}
            setRecurrenceMetadata={props.setRecurrenceMetadata}
          />
          <DateTimePicker
            label="End recurrence"
            onChange={(newVal: Date | null) =>
              newVal && props.setEndRecurrence(newVal)
            }
            defaultValue={props.endRecurrence}
          />
        </Stack>
      </DialogContent>
      <DialogActions>
        <Button onClick={props.close}>Ok</Button>
      </DialogActions>
    </Dialog>
  );
};
