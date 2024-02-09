import { Step, StepLabel, Stepper } from "@mui/material";

const steps = ["Upload image", "Select an event location", "Create the event"];

type CreateEventStepperProps = {
  readonly active: 0 | 1 | 2;
};

export const CreateEventStepper = (props: CreateEventStepperProps) => {
  return (
    <Stepper activeStep={props.active}>
      {steps.map((label) => (
        <Step key={label}>
          <StepLabel>{label}</StepLabel>
        </Step>
      ))}
    </Stepper>
  );
};
