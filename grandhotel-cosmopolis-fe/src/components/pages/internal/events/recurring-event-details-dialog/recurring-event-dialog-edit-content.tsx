import { Grid, IconButton, Stack, Typography } from "@mui/material";
import { Dispatch, ReactElement, SetStateAction, useState } from "react";
import {
  Recurrence,
  RecurringEventDto,
} from "../../../../../infrastructure/generated/openapi";
import { EditableTextField } from "../../../../shared/editable-text-field";
import Edit from "@mui/icons-material/Edit";
import { EditRecurrenceDialog } from "./recurring-event-dialog-edit-content/edit-recurrence-dialog";
import { getStringWeekday } from "../../../../../services/date-time.service";
import { EditLocationDialog } from "../single-event-details-dialog/single-event-dialog-edit-content/edit-location-dialog";
import { EditImageDialog } from "../single-event-details-dialog/single-event-dialog-edit-content/edit-image-dialog";

type RecurringEventDialogEditContentProps = {
  readonly recurringEvent?: RecurringEventDto;
  readonly setRecurringEvent: Dispatch<
    SetStateAction<RecurringEventDto | undefined>
  >;
  readonly closeDialog: () => void;
  readonly isReadOnly: boolean;
};

export const RecurringEventDialogEditContent = (
  props: RecurringEventDialogEditContentProps
) => {
  const [editDate, setEditDate] = useState(false);
  const [editLocation, setEditLocation] = useState(false);
  const [editImage, setEditImage] = useState(false);

  return (
    <Stack>
      <Grid container spacing={2} mt={4} mr={-2}>
        <Grid item xs={12} sm={6}>
          <EditableTextField
            label="Title German"
            onChange={(e) =>
              props.setRecurringEvent((curr) => ({
                ...curr,
                titleDe: e.target.value,
              }))
            }
            defaultValue={props.recurringEvent?.titleDe}
            fullWidth
            isEditable={!props.isReadOnly}
            value={props.recurringEvent?.titleDe}
          />
        </Grid>
        <Grid item xs={12} sm={6}>
          <EditableTextField
            label="Title English"
            onChange={(e) =>
              props.setRecurringEvent((curr) => ({
                ...curr,
                titleEn: e.target.value,
              }))
            }
            defaultValue={props.recurringEvent?.titleEn}
            fullWidth
            isEditable={!props.isReadOnly}
            value={props.recurringEvent?.titleEn}
          />
        </Grid>
        <Grid item xs={12} sm={6}>
          <EditableTextField
            label="Description German"
            onChange={(e) =>
              props.setRecurringEvent((curr) => ({
                ...curr,
                descriptionDe: e.target.value,
              }))
            }
            defaultValue={props.recurringEvent?.descriptionDe}
            fullWidth
            isEditable={!props.isReadOnly}
            value={props.recurringEvent?.descriptionDe}
            multiline
            rows={10}
          />
        </Grid>
        <Grid item xs={12} sm={6}>
          <EditableTextField
            label="Description English"
            onChange={(e) =>
              props.setRecurringEvent((curr) => ({
                ...curr,
                descriptionEn: e.target.value,
              }))
            }
            defaultValue={props.recurringEvent?.descriptionEn}
            fullWidth
            isEditable={!props.isReadOnly}
            value={props.recurringEvent?.descriptionEn}
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
              Recurrence
            </Typography>
            <ContentOrEditButton
              hasContent={
                !!props.recurringEvent?.recurrence &&
                !!props.recurringEvent.recurrenceMetadata
              }
              content={
                <RecurrenceDisplay
                  recurrence={props.recurringEvent?.recurrence}
                  recurrenceMetadata={props.recurringEvent?.recurrenceMetadata}
                />
              }
              onEditButtonClick={() => setEditDate(true)}
              showEditButton={!props.isReadOnly}
              placeHolder={<Typography>Set Recurrence</Typography>}
            />
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
            <ContentOrEditButton
              hasContent={!!props.recurringEvent?.eventLocation}
              content={
                <Stack>
                  <Typography>
                    {props.recurringEvent?.eventLocation?.name}
                  </Typography>
                  <Typography variant="caption">
                    {props.recurringEvent?.eventLocation?.street}
                  </Typography>
                  <Typography variant="caption">
                    {props.recurringEvent?.eventLocation?.city}
                  </Typography>
                  <Typography variant="caption">
                    {props.recurringEvent?.eventLocation?.additionalInformation}
                  </Typography>
                </Stack>
              }
              onEditButtonClick={() => setEditLocation(true)}
              showEditButton={!props.isReadOnly}
              placeHolder={<Typography>Set location</Typography>}
            />
          </Stack>
        </Grid>
        <Grid item xs={12} sm={6}>
          <Stack pl={2}>
            <Typography
              variant="caption"
              sx={(theme) => ({ color: theme.palette.text.secondary })}
            >
              Image
            </Typography>
            <ContentOrEditButton
              hasContent={!!props.recurringEvent?.image}
              content={
                <Typography>{props.recurringEvent?.image?.fileUrl}</Typography>
              }
              showEditButton={!props.isReadOnly}
              onEditButtonClick={() => setEditImage(true)}
              placeHolder={<Typography>Set image</Typography>}
            />
          </Stack>
        </Grid>
      </Grid>
      <EditRecurrenceDialog
        open={editDate}
        close={() => setEditDate(false)}
        startFirstOccurrence={props.recurringEvent?.startFirstOccurrence}
        endFirstOccurrence={props.recurringEvent?.endFirstOccurrence}
        endRecurrence={props.recurringEvent?.endRecurrence ?? undefined}
        recurrence={props.recurringEvent?.recurrence}
        recurrenceMetadata={props.recurringEvent?.recurrenceMetadata}
        setStartFirstOccurrence={(newDate) =>
          props.setRecurringEvent((curr) => ({
            ...curr,
            startFirstOccurrence: newDate,
          }))
        }
        setEndFirstOccurrence={(newDate) =>
          props.setRecurringEvent((curr) => ({
            ...curr,
            endFirstOccurrence: newDate,
          }))
        }
        setEndRecurrence={(newDate) =>
          props.setRecurringEvent((curr) => ({
            ...curr,
            endRecurrence: newDate,
          }))
        }
        setRecurrence={(newRecurrence) =>
          props.setRecurringEvent((curr) => ({
            ...curr,
            recurrence: newRecurrence,
          }))
        }
        setRecurrenceMetadata={(newRecurrenceMetadata) =>
          props.setRecurringEvent((curr) => ({
            ...curr,
            recurrenceMetadata: newRecurrenceMetadata,
          }))
        }
      />
      <EditLocationDialog
        open={editLocation}
        close={() => setEditLocation(false)}
        location={props.recurringEvent?.eventLocation}
        setLocation={(newVal) =>
          props.setRecurringEvent((curr) => ({
            ...curr,
            eventLocation: newVal,
          }))
        }
      />
      <EditImageDialog
        open={editImage}
        close={() => setEditImage(false)}
        image={props.recurringEvent?.image}
        setImage={(newVal) =>
          props.setRecurringEvent((curr) => ({ ...curr, image: newVal }))
        }
      />
    </Stack>
  );
};

type ContentOrEditButtonProps = {
  readonly showEditButton: boolean;
  readonly onEditButtonClick: () => void;
  readonly hasContent: boolean;
  readonly content: ReactElement;
  readonly placeHolder: ReactElement;
};

const ContentOrEditButton = (props: ContentOrEditButtonProps) => {
  return (
    <Stack direction="row" alignItems="center" justifyContent="space-between">
      {props.hasContent ? props.content : props.placeHolder}
      {props.showEditButton && (
        <IconButton onClick={() => props.onEditButtonClick()}>
          <Edit />
        </IconButton>
      )}
    </Stack>
  );
};

type RecurrenceDisplayProps = {
  readonly recurrence?: Recurrence;
  readonly recurrenceMetadata?: number;
};

const RecurrenceDisplay = (props: RecurrenceDisplayProps) => {
  const getDisplayValue = (recurrence: Recurrence) => {
    switch (recurrence) {
      case Recurrence.FirstDayInMonth:
        return "ersten";
      case Recurrence.SecondDayInMonth:
        return "zweiten";
      case Recurrence.ThirdDayInMonth:
        return "dritten";
      case Recurrence.LastDayInMonth:
        return "letzten";
    }
  };

  if (props.recurrence === Recurrence.XDays) {
    return <Typography>Alle {props.recurrenceMetadata} Tage</Typography>;
  }
  if (props.recurrence === Recurrence.MonthAtDayX) {
    return <Typography>Jeden {props.recurrenceMetadata}. im Monat</Typography>;
  }

  if (props.recurrence !== undefined) {
    return (
      <Typography>
        Jeden {getDisplayValue(props.recurrence)}{" "}
        {getStringWeekday(props.recurrenceMetadata)} des Monats
      </Typography>
    );
  }
};
