import {
  Box,
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  Stack,
  Typography,
} from "@mui/material";
import { SingleEventDialogContent } from "./single-event-dialog-content";
import EditIcon from "@mui/icons-material/Edit";
import VisibilityIcon from "@mui/icons-material/Visibility";
import VisibilityOffIcon from "@mui/icons-material/VisibilityOff";
import EditOffIcon from "@mui/icons-material/EditOff";
import {
  EventLocationDto,
  SingleEventDto,
} from "../../../../infrastructure/generated/openapi";
import { useState } from "react";
import { useIsMobileView } from "../../../hooks/screen-sizes/use-is-mobile-view";

type SingleEventDetailsDialogProps = {
  readonly open: boolean;
  readonly singleEvent?: SingleEventDto;
  readonly closeDialog: () => void;
};

export const SingleEventDetailsDialog = (
  props: SingleEventDetailsDialogProps
) => {
  const [editMode, setEditMode] = useState(false);
  const [preview, setPreview] = useState(false);

  return (
    <Dialog open={props.open} fullWidth maxWidth="xl">
      <DialogTitle>
        <Box display="flex" justifyContent="space-between">
          <Typography>Event details</Typography>
          <Stack direction="row" spacing={2}>
            <Button
              variant={preview ? "contained" : "outlined"}
              startIcon={preview ? <VisibilityOffIcon /> : <VisibilityIcon />}
              onClick={() =>
                setPreview((curr) => {
                  if (!curr) {
                    setEditMode(false);
                  }
                  return !curr;
                })
              }
            >
              Preview
            </Button>
            <Button
              variant={editMode ? "contained" : "outlined"}
              startIcon={editMode ? <EditOffIcon /> : <EditIcon />}
              onClick={() =>
                setEditMode((curr) => {
                  if (!curr) {
                    setPreview(false);
                  }
                  return !curr;
                })
              }
            >
              Edit
            </Button>
          </Stack>
        </Box>
      </DialogTitle>
      <DialogContent
        sx={{ height: "80vh", display: "flex", flexDirection: "column" }}
      >
        {preview ? (
          <Test event={props.singleEvent} />
        ) : (
          <SingleEventDialogContent
            isReadOnly={!editMode}
            closeDialog={props.closeDialog}
            singleEvent={props.singleEvent}
          />
        )}
      </DialogContent>
      <DialogActions>
        <Button onClick={props.closeDialog}>Close</Button>
      </DialogActions>
    </Dialog>
  );
};

const textFormatter = (text: string) => {
  return text.split("\n").map((str, i) => <p key={i}>{str}</p>);
};

const Test = ({ event }: { event: SingleEventDto }) => {
  const isMobileView = useIsMobileView();
  return (
    <Box>
      <Box
        display="flex"
        width="100%"
        flexDirection="row"
        sx={{ mb: 4, mt: 4 }}
      >
        <Box width="100px" sx={{ mr: 2 }}>
          <DateIndicator start={event.start} end={event.end} />
        </Box>
        <Box display="flex" width="100%">
          <Box width="100%">
            <Box width="100%">
              <img
                style={{
                  float: "right",
                  width: isMobileView ? "100%" : "40%",
                  marginLeft: 32,
                  marginBottom: isMobileView ? 16 : 0,
                }}
                src={event.image?.fileUrl}
              />
            </Box>
            <Stack>
              <Typography variant="h5">{event.titleDe}</Typography>
              <WhenIndicator start={event.start} end={event.end} />
              <WhereIndicator eventLocation={event.eventLocation} />
            </Stack>
            <Typography
              component="span"
              variant={isMobileView ? "body2" : "body1"}
            >
              {textFormatter(event.descriptionDe ?? "")}
            </Typography>
          </Box>
        </Box>
      </Box>
    </Box>
  );
};

type WhenIndicatorProps = {
  readonly start?: Date;
  readonly end?: Date;
};

const WhenIndicator = (props: WhenIndicatorProps) => {
  if (
    props.start?.getMonth() === props.end?.getMonth() &&
    props.start?.getDate() == props.start?.getDate()
  ) {
    return (
      <Typography>
        Wann: {props.start?.toLocaleDateString("de-DE")}{" "}
        {props.start?.toLocaleTimeString("de-DE", {
          minute: "2-digit",
          hour: "2-digit",
        })}{" "}
        -{" "}
        {props.end?.toLocaleTimeString("de-DE", {
          minute: "2-digit",
          hour: "2-digit",
        })}
      </Typography>
    );
  }

  return (
    <Typography>
      Wann: {props.start?.toLocaleDateString("de-DE")}{" "}
      {props.start?.toLocaleTimeString("de-DE", {
        hour: "2-digit",
        minute: "2-digit",
      })}{" "}
      -{props.end?.toLocaleDateString("de-DE")}{" "}
      {props.end?.toLocaleTimeString("de-DE", {
        hour: "2-digit",
        minute: "2-digit",
      })}
    </Typography>
  );
};

type WhereIndicatorProps = {
  readonly eventLocation?: EventLocationDto;
};

export const WhereIndicator = (props: WhereIndicatorProps) => {
  return (
    <Stack direction="row" spacing={1}>
      <Typography>Wo:</Typography>
      <Stack>
        <Typography>{props.eventLocation?.name}</Typography>
        <Typography variant="caption">{props.eventLocation?.street}</Typography>
        <Typography variant="caption">{props.eventLocation?.city}</Typography>
      </Stack>
    </Stack>
  );
};

type DateIndicatorProps = {
  readonly start?: Date;
  readonly end?: Date;
};

const DateIndicator = (props: DateIndicatorProps) => {
  return (
    <Stack alignItems="center">
      <Typography variant="overline" lineHeight={1.2}>
        {props.start?.toLocaleDateString("de-DE", {
          month: "short",
        })}
      </Typography>
      <Typography lineHeight={1.2}>{props.start?.getDate()}</Typography>
      <Typography variant="caption">{props.start?.getFullYear()}</Typography>
    </Stack>
  );
};
