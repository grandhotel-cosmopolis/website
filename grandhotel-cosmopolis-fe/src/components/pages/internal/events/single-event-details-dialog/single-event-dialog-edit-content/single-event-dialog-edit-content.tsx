import { Button, Grid, IconButton, Stack, Typography } from "@mui/material";
import { Dispatch, ReactElement, SetStateAction, useState } from "react";
import { SingleEventDto } from "../../../../../../infrastructure/generated/openapi";
import { Edit } from "@mui/icons-material";
import { EditDateDialog } from "./edit-date-dialog";
import { EditableTextField } from "../../../../../shared/editable-text-field";
import { EditLocationDialog } from "./edit-location-dialog";
import { EditImageDialog } from "./edit-image-dialog";
import { Mode } from "../single-event-details-dialog";
import { SingleEventDate } from "../../../../../shared/date/single-event-date";
import { SingleEventTypography } from "../../../../../shared/single-event-typography";
import { EventLocation } from "../../../../../shared/location/event-location";

type SingleEventDialogEditContentProps = {
  readonly singleEvent?: SingleEventDto;
  readonly setSingleEvent: Dispatch<SetStateAction<SingleEventDto | undefined>>;
  readonly closeDialog: () => void;
  readonly isReadOnly: boolean;
  readonly mode: Mode;
};

export const SingleEventDialogEditContent = (
  props: SingleEventDialogEditContentProps
) => {
  const [editDate, setEditDate] = useState(false);
  const [editLocation, setEditLocation] = useState(false);
  const [editImage, setEditImage] = useState(false);
  const [relocateEvent, setRelocateEvent] = useState(false);
  const [rescheduleEvent, setRescheduleEvent] = useState(false);

  return (
    <Stack>
      <Grid container spacing={2} mt={4} mr={-2}>
        <Grid item xs={12} sm={6}>
          <EditableTextField
            singleEvent={props.singleEvent}
            label="Title German"
            onChange={(e) =>
              props.setSingleEvent((curr) => ({
                ...curr,
                titleDe: e.target.value,
              }))
            }
            defaultValue={props.singleEvent?.titleDe}
            fullWidth
            isEditable={!props.isReadOnly}
            value={props.singleEvent?.titleDe}
          />
        </Grid>
        <Grid item xs={12} sm={6}>
          <EditableTextField
            singleEvent={props.singleEvent}
            label="Title English"
            onChange={(e) =>
              props.setSingleEvent((curr) => ({
                ...curr,
                titleEn: e.target.value,
              }))
            }
            defaultValue={props.singleEvent?.titleEn}
            fullWidth
            isEditable={!props.isReadOnly}
            value={props.singleEvent?.titleEn}
          />
        </Grid>
        <Grid item xs={12} sm={6}>
          <EditableTextField
            singleEvent={props.singleEvent}
            label="Description German"
            onChange={(e) =>
              props.setSingleEvent((curr) => ({
                ...curr,
                descriptionDe: e.target.value,
              }))
            }
            defaultValue={props.singleEvent?.descriptionDe}
            fullWidth
            isEditable={!props.isReadOnly}
            value={props.singleEvent?.descriptionDe}
            multiline
            rows={10}
          />
        </Grid>
        <Grid item xs={12} sm={6}>
          <EditableTextField
            singleEvent={props.singleEvent}
            label="Description English"
            onChange={(e) =>
              props.setSingleEvent((curr) => ({
                ...curr,
                descriptionEn: e.target.value,
              }))
            }
            defaultValue={props.singleEvent?.descriptionEn}
            fullWidth
            isEditable={!props.isReadOnly}
            value={props.singleEvent?.descriptionEn}
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
            <Stack direction="row" width="100%" spacing={1}>
              <ContentOrEditButton
                hasContent={!!props.singleEvent?.start}
                content={<SingleEventDate singleEvent={props.singleEvent} />}
                onEditButtonClick={() => setEditDate(true)}
                showEditButton={!props.isReadOnly}
                placeHolder={<Typography>Set Date</Typography>}
              />
              {props.mode === "Update" && !props.isReadOnly && (
                <Stack justifyContent="center">
                  <Button
                    variant="outlined"
                    onClick={() => setRescheduleEvent(true)}
                  >
                    ReSchedule
                  </Button>
                </Stack>
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
            <Stack direction="row" width="100%" spacing={1}>
              <ContentOrEditButton
                hasContent={!!props.singleEvent?.eventLocation}
                content={<EventLocation singleEvent={props.singleEvent} />}
                onEditButtonClick={() => setEditLocation(true)}
                showEditButton={!props.isReadOnly}
                placeHolder={<Typography>Set location</Typography>}
              />
              {props.mode === "Update" && !props.isReadOnly && (
                <Stack justifyContent="center">
                  <Button
                    variant="outlined"
                    onClick={() => setRelocateEvent(true)}
                  >
                    Relocate
                  </Button>
                </Stack>
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
              Image
            </Typography>
            <Stack direction="row" width="100%" spacing={1}>
              <ContentOrEditButton
                hasContent={!!props.singleEvent?.image}
                content={
                  <SingleEventTypography singleEvent={props.singleEvent}>
                    {props.singleEvent?.image?.fileUrl}
                  </SingleEventTypography>
                }
                showEditButton={!props.isReadOnly}
                onEditButtonClick={() => setEditImage(true)}
                placeHolder={<Typography>Set image</Typography>}
              />
            </Stack>
          </Stack>
        </Grid>
      </Grid>
      <EditDateDialog
        open={editDate}
        start={props.singleEvent?.start}
        end={props.singleEvent?.end}
        setEnd={(newVal) =>
          props.setSingleEvent((curr) => ({
            ...curr,
            end: newVal,
          }))
        }
        setStart={(newVal) =>
          props.setSingleEvent((curr) => ({
            ...curr,
            start: newVal,
          }))
        }
        close={() => setEditDate(false)}
      />
      <EditDateDialog
        open={rescheduleEvent}
        start={props.singleEvent?.exception?.start ?? undefined}
        end={props.singleEvent?.exception?.end ?? undefined}
        setStart={(newVal) =>
          props.setSingleEvent((curr) => ({
            ...curr,
            exception: {
              ...curr?.exception,
              start: newVal,
            },
          }))
        }
        setEnd={(newVal) =>
          props.setSingleEvent((curr) => ({
            ...curr,
            exception: {
              ...curr?.exception,
              end: newVal,
            },
          }))
        }
        close={() => setRescheduleEvent(false)}
      />
      <EditLocationDialog
        open={editLocation}
        close={() => setEditLocation(false)}
        location={props.singleEvent?.eventLocation}
        setLocation={(newVal) =>
          props.setSingleEvent((curr) => ({
            ...curr,
            eventLocation: newVal,
          }))
        }
      />
      <EditLocationDialog
        open={relocateEvent}
        close={() => setRelocateEvent(false)}
        location={props.singleEvent?.exception?.eventLocation}
        setLocation={(newVal) =>
          props.setSingleEvent((curr) => ({
            ...curr,
            exception: {
              ...curr?.exception,
              eventLocation: newVal,
            },
          }))
        }
      />
      <EditImageDialog
        open={editImage}
        close={() => setEditImage(false)}
        image={props.singleEvent?.image}
        setImage={(newVal) =>
          props.setSingleEvent((curr) => ({ ...curr, image: newVal }))
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
    <Stack
      direction="row"
      alignItems="center"
      justifyContent="space-between"
      width="100%"
    >
      {props.hasContent ? props.content : props.placeHolder}
      {props.showEditButton && (
        <IconButton onClick={() => props.onEditButtonClick()}>
          <Edit />
        </IconButton>
      )}
    </Stack>
  );
};
