import { TextField, Stack, Typography } from "@mui/material";
import { ChangeEvent } from "react";
import { SingleEventTypography } from "./single-event-typography";
import { SingleEventDto } from "../../infrastructure/generated/openapi";

type EditableTextFieldProps = {
  readonly fullWidth?: boolean;
  readonly label: string;
  readonly onChange: (e: ChangeEvent<HTMLInputElement>) => void;
  readonly defaultValue?: string;
  readonly multiline?: boolean;
  readonly rows?: number;
  readonly isEditable?: boolean;
  readonly value?: string;
  readonly singleEvent?: SingleEventDto;
};

export const EditableTextField = (props: EditableTextFieldProps) => {
  return (
    <>
      {props.isEditable ? (
        <TextField
          multiline={props.multiline}
          rows={props.rows ?? 1}
          fullWidth={props.fullWidth}
          label={props.label}
          onChange={props.onChange}
          defaultValue={props.defaultValue}
        />
      ) : (
        <Stack pl={2}>
          <Typography
            variant="caption"
            sx={(theme) => ({ color: theme.palette.text.secondary })}
          >
            {props.label}
          </Typography>
          <SingleEventTypography singleEvent={props.singleEvent}>
            {props.value}
          </SingleEventTypography>
        </Stack>
      )}
    </>
  );
};
