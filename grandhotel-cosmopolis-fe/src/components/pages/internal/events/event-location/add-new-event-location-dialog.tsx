import {
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  Stack,
  TextField,
} from "@mui/material";
import { EventLocationDto } from "../../../../../infrastructure/generated/openapi";

type AddNewEventLocationDialogProps = {
  readonly open: boolean;
  readonly onClose: () => void;
  readonly onSubmit: (_: React.FormEvent<HTMLFormElement>) => void;
  readonly setDialogValue: React.Dispatch<
    React.SetStateAction<Omit<EventLocationDto, "guid"> | undefined>
  >;
  readonly dialogValue?: Omit<EventLocationDto, "guid">;
};

export const AddNewEventLocationDialog = (
  props: AddNewEventLocationDialogProps
) => {
  return (
    <Dialog open={props.open} onClose={props.onClose}>
      <form onSubmit={props.onSubmit}>
        <DialogTitle>Add a new Event location</DialogTitle>
        <DialogContent>
          <Stack>
            <TextField
              autoFocus
              margin="dense"
              value={props.dialogValue?.name}
              onChange={(event) =>
                props.setDialogValue({
                  ...props.dialogValue,
                  name: event.target.value,
                })
              }
              label="name"
              type="text"
              variant="standard"
            />
            <TextField
              margin="dense"
              value={props.dialogValue?.street}
              onChange={(event) =>
                props.setDialogValue({
                  ...props.dialogValue,
                  street: event.target.value,
                })
              }
              label="street"
              type="text"
              variant="standard"
            />
            <TextField
              margin="dense"
              value={props.dialogValue?.city}
              onChange={(event) =>
                props.setDialogValue({
                  ...props.dialogValue,
                  city: event.target.value,
                })
              }
              label="city"
              type="text"
              variant="standard"
            />
          </Stack>
        </DialogContent>
        <DialogActions>
          <Button onClick={props.onClose}>Cancel</Button>
          <Button type="submit">Add</Button>
        </DialogActions>
      </form>
    </Dialog>
  );
};
