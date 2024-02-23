import { Grid, IconButton, Stack, Typography } from "@mui/material";
import { Dispatch, ReactElement, SetStateAction, useState } from "react";
import { SingleEventDto } from "../../../../../../infrastructure/generated/openapi";
import { Edit } from "@mui/icons-material";
import { renderDateCell } from "../../single-event-tab/single-events-tab";
import { EditDateDialog } from "./edit-date-dialog";
import { EditableTextField } from "../../../../../shared/editable-text-field";
import { EditLocationDialog } from "./edit-location-dialog";
import { EditImageDialog } from "./edit-image-dialog";

type SingleEventDialogEditContentProps = {
  readonly singleEvent?: SingleEventDto;
  readonly setSingleEvent: Dispatch<SetStateAction<SingleEventDto | undefined>>;
  readonly closeDialog: () => void;
  readonly isReadOnly: boolean;
};

export const SingleEventDialogEditContent = (
  props: SingleEventDialogEditContentProps
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
            <ContentOrEditButton
              hasContent={!!props.singleEvent?.start}
              content={renderDateCell(
                props.singleEvent?.start,
                props.singleEvent?.end
              )}
              onEditButtonClick={() => setEditDate(true)}
              showEditButton={!props.isReadOnly}
              placeHolder={<Typography>Set Date</Typography>}
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
              hasContent={!!props.singleEvent?.eventLocation}
              content={
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
                  <Typography variant="caption">
                    {props.singleEvent?.eventLocation?.additionalInformation}
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
              hasContent={!!props.singleEvent?.image}
              content={
                <Typography>{props.singleEvent?.image?.fileUrl}</Typography>
              }
              showEditButton={!props.isReadOnly}
              onEditButtonClick={() => setEditImage(true)}
              placeHolder={<Typography>Set image</Typography>}
            />
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
