import {
  Dialog,
  DialogContent,
  Stack,
  Typography,
  Switch,
  DialogActions,
  Button,
} from "@mui/material";
import { DateTimePicker, DatePicker, TimePicker } from "@mui/x-date-pickers";
import { useState } from "react";

type EditDateDialogProps = {
  readonly open: boolean;
  readonly start?: Date;
  readonly end?: Date;
  readonly setStart: (_: Date) => void;
  readonly setEnd: (_: Date) => void;
  readonly close: () => void;
};

export const EditDateDialog = (props: EditDateDialogProps) => {
  const [multipleDays, setMultipleDays] = useState(
    props.start?.getDate() !== props.end?.getDate()
  );
  const [date, setDate] = useState(props.start);
  const [start, setStart] = useState(props.start);
  const [end, setEnd] = useState(props.end);

  const handleStartChange = (newValue: Date | null) => {
    if (!!newValue) {
      setStart(newValue);
      if (!!date) {
        props.setStart(
          new Date(
            date.getFullYear(),
            date.getMonth(),
            date.getDate(),
            newValue.getHours(),
            newValue.getMinutes()
          )
        );
      }
    }
  };

  const handleEndChange = (newValue: Date | null) => {
    if (!!newValue) {
      setEnd(newValue);
      if (!!date) {
        props.setEnd(
          new Date(
            date.getFullYear(),
            date.getMonth(),
            date.getDate(),
            newValue.getHours(),
            newValue.getMinutes()
          )
        );
      }
    }
  };

  const handleDateChange = (newValue: Date | null) => {
    if (!!newValue) {
      setDate(newValue);
      if (!!start) {
        props.setStart(
          new Date(
            newValue.getFullYear(),
            newValue.getMonth(),
            newValue.getDate(),
            start.getHours(),
            start.getMinutes()
          )
        );
      }
      if (!!end) {
        props.setEnd(
          new Date(
            newValue.getFullYear(),
            newValue.getMonth(),
            newValue.getDate(),
            end.getHours(),
            end.getMinutes()
          )
        );
      }
    }
  };

  return (
    <Dialog open={props.open}>
      <DialogContent>
        <Stack spacing={2}>
          <Stack direction="row" alignItems="center">
            <Typography>Event dauert meherere Tage:</Typography>
            <Switch
              checked={multipleDays}
              onChange={(e) => setMultipleDays(e.target.checked)}
            />
          </Stack>
          {multipleDays ? (
            <>
              <DateTimePicker
                label="Start"
                onChange={(newValue: Date | null) =>
                  !!newValue && props.setStart(newValue)
                }
                defaultValue={props.start}
              />
              <DateTimePicker
                label="End"
                onChange={(newValue: Date | null) =>
                  !!newValue && props.setEnd(newValue)
                }
                defaultValue={props.end}
              />
            </>
          ) : (
            <>
              <DatePicker
                label="Day"
                onChange={(newValue: Date | null) => handleDateChange(newValue)}
                defaultValue={props.start}
              />
              <TimePicker
                label="Start"
                onChange={(newVal: Date | null) => handleStartChange(newVal)}
                defaultValue={props.start}
              />
              <TimePicker
                label="End"
                onChange={(newVal: Date | null) => handleEndChange(newVal)}
                defaultValue={props.end}
              />
            </>
          )}
        </Stack>
      </DialogContent>
      <DialogActions>
        <Button onClick={props.close}>Ok</Button>
      </DialogActions>
    </Dialog>
  );
};
