import {
  Box,
  Button,
  Grid,
  IconButton,
  Stack,
  Typography,
} from "@mui/material";
import { eventApi } from "../../../../infrastructure/api";
import { useEffect, useState } from "react";
import {
  EventLocationDto,
  FileDto,
  SingleEventDto,
} from "../../../../infrastructure/generated/openapi";
import { Edit } from "@mui/icons-material";
import { renderDateCell } from "./single-events-tab";
import { EditDateDialog } from "./edit-date-dialog";
import { EditableTextField } from "../../../shared/editable-text-field";
import { EditLocationDialog } from "./edit-location-dialog";

type SingleEventDialogContentProps = {
  readonly singleEvent?: SingleEventDto;
  readonly eventLocation?: EventLocationDto;
  readonly fileUpload?: FileDto;
  readonly closeDialog: () => void;
  readonly isReadOnly: boolean;
};

export const SingleEventDialogContent = (
  props: SingleEventDialogContentProps
) => {
  const [titleDe, setTitleDe] = useState<string | undefined>(
    props.singleEvent?.titleDe
  );
  const [titleEn, setTitleEn] = useState<string | undefined>(
    props.singleEvent?.titleEn
  );
  const [descriptionDe, setDescriptionDe] = useState<string | undefined>(
    props.singleEvent?.descriptionDe
  );
  const [descriptionEn, setDescriptionEn] = useState<string | undefined>(
    props.singleEvent?.descriptionEn
  );
  const [start, setStart] = useState<Date | undefined>(
    props.singleEvent?.start
  );
  const [end, setEnd] = useState<Date | undefined>(props.singleEvent?.end);
  const [location, setLocation] = useState<EventLocationDto | undefined>(
    props.singleEvent?.eventLocation
  );

  const [savedSuccessfully, setSavedSuccessfully] = useState(false);
  const [saveError, setSaveError] = useState(false);
  const [saveEnabled, setSaveEnabled] = useState(false);

  const [editDate, setEditDate] = useState(false);
  const [editLocation, setEditLocation] = useState(false);

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
      <Grid container spacing={2} mt={4} pr={4}>
        <Grid item xs={12} sm={6}>
          <EditableTextField
            label="Title German"
            onChange={(e) => setTitleDe(e.target.value)}
            defaultValue={titleDe}
            fullWidth
            isEditable={!props.isReadOnly}
            value={titleDe}
          />
        </Grid>
        <Grid item xs={12} sm={6}>
          <EditableTextField
            label="Title English"
            onChange={(e) => setTitleEn(e.target.value)}
            defaultValue={titleEn}
            fullWidth
            isEditable={!props.isReadOnly}
            value={titleEn}
          />
        </Grid>
        <Grid item xs={12} sm={6}>
          <EditableTextField
            label="Description German"
            onChange={(e) => setDescriptionDe(e.target.value)}
            defaultValue={descriptionDe}
            fullWidth
            isEditable={!props.isReadOnly}
            value={descriptionDe}
            multiline
            rows={10}
          />
        </Grid>
        <Grid item xs={12} sm={6}>
          <EditableTextField
            label="Description English"
            onChange={(e) => setDescriptionEn(e.target.value)}
            defaultValue={descriptionEn}
            fullWidth
            isEditable={!props.isReadOnly}
            value={descriptionEn}
            multiline
            rows={10}
          />
        </Grid>
        <Grid item xs={12} sm={6}>
          <Stack pl={2}>
            <Typography
              variant="caption"
              sx={(theme) => ({ color: theme.palette.text.secondary })}
            >
              Date
            </Typography>
            <Stack direction="row" alignItems="center">
              {renderDateCell(start, end)}
              {!props.isReadOnly && (
                <IconButton onClick={() => setEditDate(true)}>
                  <Edit />
                </IconButton>
              )}
            </Stack>
          </Stack>
        </Grid>
        <Grid item xs={12} sm={6}>
          <Stack pl={2}>
            <Typography
              variant="caption"
              sx={(theme) => ({ color: theme.palette.text.secondary })}
            >
              Location
            </Typography>
            <Stack direction="row" alignItems="center">
              <Stack>
                <Typography>
                  {props.singleEvent?.eventLocation?.name}
                </Typography>
                <Typography variant="caption">
                  {props.singleEvent?.eventLocation?.street}
                </Typography>
                <Typography variant="caption">
                  {props.singleEvent?.eventLocation?.city}
                </Typography>
              </Stack>
              {!props.isReadOnly && (
                <IconButton onClick={() => setEditLocation(true)}>
                  <Edit />
                </IconButton>
              )}
            </Stack>
          </Stack>
        </Grid>
      </Grid>
      <EditDateDialog
        open={editDate}
        start={start}
        end={end}
        setEnd={setEnd}
        setStart={setStart}
        close={() => setEditDate(false)}
      />
      <EditLocationDialog
        open={editLocation}
        close={() => setEditLocation(false)}
        location={location}
        setLocation={setLocation}
      />
      <Box>
        <Button
          disabled={!saveEnabled}
          variant="outlined"
          onClick={handleSave}
          sx={{ mt: 2 }}
        >
          Save
        </Button>
      </Box>
    </Stack>
  );
};
