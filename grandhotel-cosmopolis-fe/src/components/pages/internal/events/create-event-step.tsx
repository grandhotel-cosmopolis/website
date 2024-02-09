import { Button, Grid, Stack, TextField, Typography } from "@mui/material";
import { DateTimePicker } from "@mui/x-date-pickers";
import { eventApi } from "../../../../infrastructure/api";
import { useEffect, useState } from "react";
import {
  EventLocationDto,
  FileDto,
} from "../../../../infrastructure/generated/openapi";

type CreateEventStepProps = {
  readonly eventLocation?: EventLocationDto;
  readonly fileUpload?: FileDto;
  readonly closeDialog: () => void;
};

export const CreateEventStep = (props: CreateEventStepProps) => {
  const [titleDe, setTitleDe] = useState<string>();
  const [titleEn, setTitleEn] = useState<string>();
  const [descriptionDe, setDescriptionDe] = useState<string>();
  const [descriptionEn, setDescriptionEn] = useState<string>();
  const [start, setStart] = useState<Date>();
  const [end, setEnd] = useState<Date>();

  const [savedSuccessfully, setSavedSuccessfully] = useState(false);
  const [saveError, setSaveError] = useState(false);
  const [saveEnabled, setSaveEnabled] = useState(false);

  useEffect(() => {
    if (
      !!titleDe &&
      !!titleEn &&
      !!descriptionDe &&
      !!descriptionEn &&
      !!start &&
      !!end
    ) {
      setSaveEnabled(true);
    } else {
      setSaveEnabled(false);
    }
  }, [titleDe, titleEn, descriptionDe, descriptionEn, start, end]);

  const handleSave = () => {
    if (saveEnabled) {
      eventApi
        .addSingleEvent(
          titleDe,
          titleEn,
          descriptionDe,
          descriptionEn,
          start,
          end,
          props.eventLocation?.guid,
          props.fileUpload?.guid
        )
        .then(() => setSavedSuccessfully(true))
        .catch(() => setSaveError(true));
    }
  };

  if (savedSuccessfully) {
    return (
      <Stack>
        <Typography>Alles geklappt</Typography>
        <Button variant="outlined" sx={{ mt: 2 }} onClick={props.closeDialog}>
          Close
        </Button>
      </Stack>
    );
  }

  if (saveError) {
    return (
      <Stack>
        <Typography>
          Ein fehler ist augetreten. Später erneut versuchen
        </Typography>
        <Button variant="outlined" sx={{ mt: 2 }} onClick={props.closeDialog}>
          Close
        </Button>
      </Stack>
    );
  }

  return (
    <Stack spacing={2}>
      <Grid container spacing={2} mt={4}>
        <Grid item xs={12} sm={6}>
          <TextField
            fullWidth
            autoFocus
            label="Title German"
            onChange={(e) => setTitleDe(e.target.value)}
          />
        </Grid>
        <Grid item xs={12} sm={6}>
          <TextField
            fullWidth
            label="Title English"
            onChange={(e) => setTitleEn(e.target.value)}
          />
        </Grid>
        <Grid item xs={12} sm={6}>
          <TextField
            multiline
            rows={10}
            fullWidth
            label="Description German"
            onChange={(e) => setDescriptionDe(e.target.value)}
          />
        </Grid>
        <Grid item xs={12} sm={6}>
          <TextField
            multiline
            rows={10}
            fullWidth
            label="Description English"
            onChange={(e) => setDescriptionEn(e.target.value)}
          />
        </Grid>
        <Grid item xs={12} sm={6}>
          <DateTimePicker
            sx={{ width: "100%" }}
            label="Start"
            onChange={(newValue: Date | null) =>
              setStart(newValue ?? undefined)
            }
          />
        </Grid>
        <Grid item xs={12} sm={6}>
          <DateTimePicker
            sx={{ width: "100%" }}
            label="End"
            onChange={(newValue: Date | null) => setEnd(newValue ?? undefined)}
          />
        </Grid>
      </Grid>
      <Button
        disabled={!saveEnabled}
        variant="outlined"
        onClick={handleSave}
        sx={{ mt: 2 }}
      >
        Save
      </Button>
    </Stack>
  );
};
