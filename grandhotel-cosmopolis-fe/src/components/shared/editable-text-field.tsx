import { TextField, Stack, Typography } from "@mui/material";
import { ChangeEvent } from "react";

type EditableTextFieldProps = {
  readonly fullWidth?: boolean;
  readonly label: string;
  readonly onChange: (e: ChangeEvent<HTMLInputElement>) => void;
  readonly defaultValue?: string;
  readonly multiline?: boolean;
  readonly rows?: number;
  readonly isEditable?: boolean;
  readonly value?: string;
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
          <Typography>{props.value}</Typography>
        </Stack>
      )}
    </>
  );
};
