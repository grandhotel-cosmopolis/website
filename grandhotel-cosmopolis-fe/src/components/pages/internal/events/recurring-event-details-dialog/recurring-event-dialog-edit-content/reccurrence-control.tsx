import {
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Stack,
  Typography,
  TextField,
  SelectChangeEvent,
} from "@mui/material";
import { Recurrence } from "../../../../../../infrastructure/generated/openapi";
import { getDisplayValueForRecurrence } from "../../../../../shared/text-formatter";
import { getStringWeekday } from "../../../../../../services/date-time.service";
import { useState } from "react";

type RecurrenceControlProps = {
  readonly recurrence?: Recurrence;
  readonly setRecurrence: (_: Recurrence) => void;
  readonly recurrenceMetadata?: number;
  readonly setRecurrenceMetadata: (_?: number) => void;
};

export const RecurrenceControl = (props: RecurrenceControlProps) => {
  const [metadata, setMetadata] = useState<string>(
    props.recurrenceMetadata?.toString() ?? ""
  );

  const handleChangeMetadata = (event: SelectChangeEvent) => {
    if (!isNaN(Number(event.target.value))) {
      props.setRecurrenceMetadata(Number(event.target.value));
      setMetadata(event.target.value);
      return;
    }
    if (event.target.value === "") {
      setMetadata(event.target.value);
    }
  };

  const getRecurrenceInput = (recurrence: Recurrence) => {
    if (recurrence === Recurrence.XDays) {
      return (
        <>
          <TextField
            inputProps={{ maxLength: 3 }}
            label="Tage"
            onChange={(e) => {
              setMetadata(e.target.value);
              if (!isNaN(Number(e.target.value))) {
                props.setRecurrenceMetadata(Number(e.target.value));
              }
            }}
            value={metadata}
          />
          <Typography>Tage</Typography>
        </>
      );
    }
    if (recurrence === Recurrence.MonthAtDayX) {
      return (
        <TextField
          inputProps={{ maxLength: 2 }}
          label="Tag"
          onChange={(e) => {
            setMetadata(e.target.value);
            if (!isNaN(Number(e.target.value))) {
              props.setRecurrenceMetadata(Number(e.target.value));
            }
          }}
          value={metadata}
        />
      );
    }
    return (
      <FormControl fullWidth>
        <InputLabel>Metadata</InputLabel>
        <Select
          value={metadata}
          label="Metadata"
          onChange={handleChangeMetadata}
        >
          {[...Array(7).keys()].map((wd, i) => (
            <MenuItem key={i} value={wd}>
              {getStringWeekday(wd)}
            </MenuItem>
          ))}
        </Select>
      </FormControl>
    );
  };
  return (
    <Stack spacing={2}>
      <Typography>Wiederholung</Typography>
      <Stack spacing={2} px={2}>
        <FormControl fullWidth>
          <InputLabel>Wiederhohlung</InputLabel>
          <Select
            value={props.recurrence}
            label="Wiederhohlung"
            onChange={(newVal) => {
              const newRecurrence = newVal.target.value as Recurrence;
              if (newRecurrence !== props.recurrence) {
                setMetadata("");
                props.setRecurrenceMetadata(undefined);
              }
              props.setRecurrence(newVal.target.value as Recurrence);
            }}
          >
            {[...Object.values(Recurrence)].map((r, i) => (
              <MenuItem key={i} value={r}>
                {getDisplayValueForRecurrence(r)}
              </MenuItem>
            ))}
          </Select>
        </FormControl>
        <Stack direction="row" alignItems="center" spacing={2}>
          {getRecurrenceInput(props.recurrence ?? Recurrence.XDays)}
        </Stack>
      </Stack>
    </Stack>
  );
};
