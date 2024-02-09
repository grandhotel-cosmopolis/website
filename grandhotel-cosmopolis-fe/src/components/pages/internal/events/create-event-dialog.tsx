import { Dialog, DialogContent, Box } from "@mui/material";
import { CreateEventStepper } from "./create-event-setpper";
import { useState } from "react";
import { UploadFileStep } from "./upload-file-step";
import {
  EventLocationDto,
  FileDto,
} from "../../../../infrastructure/generated/openapi";
import { EventLocationStep } from "./event-location-step";
import { CreateEventStep } from "./create-event-step";

type CreateEventDialogProps = {
  readonly open: boolean;
  readonly close: () => void;
};

export const CreateEventDialog = (props: CreateEventDialogProps) => {
  const [activeStep, setActiveStep] = useState<0 | 1 | 2>(0);
  const [uploadedFile, setUploadedFile] = useState<FileDto>();
  const [selectedEventLocation, setSelectedEventLocation] =
    useState<EventLocationDto>();

  const getContent = () => {
    if (activeStep === 0) {
      return (
        <UploadFileStep
          finish={() => setActiveStep(1)}
          setUploadedFile={setUploadedFile}
        />
      );
    }
    if (activeStep === 1) {
      return (
        <EventLocationStep
          finish={() => setActiveStep(2)}
          setSelectedEventLocation={setSelectedEventLocation}
        />
      );
    }
    return (
      <CreateEventStep
        eventLocation={selectedEventLocation}
        fileUpload={uploadedFile}
        closeDialog={props.close}
      />
    );
  };

  return (
    <Dialog open={props.open} fullWidth maxWidth="xl">
      <DialogContent
        sx={{ height: "80vh", display: "flex", flexDirection: "column" }}
      >
        <CreateEventStepper active={activeStep} />
        <Box
          display="flex"
          justifyContent="center"
          alignItems="center"
          flexGrow={1}
        >
          {getContent()}
        </Box>
      </DialogContent>
    </Dialog>
  );
};
