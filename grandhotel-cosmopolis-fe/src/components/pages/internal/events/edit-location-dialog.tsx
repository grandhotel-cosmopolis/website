import {
  Dialog,
  DialogContent,
  DialogActions,
  Button,
  Stack,
  Typography,
  Switch,
  TextField,
  FormControl,
  InputLabel,
  MenuItem,
  Select,
} from "@mui/material";
import { SelectChangeEvent } from "@mui/material";
import { EventLocationDto } from "../../../../infrastructure/generated/openapi";
import { useEffect, useState } from "react";
import { eventLocationApi } from "../../../../infrastructure/api";
import { AddEventLocationAutocomplete } from "./event-location/add-event-location-autocomplete";
import { AddNewEventLocationDialog } from "./event-location/add-new-event-location-dialog";
import { fixEventLocations } from "./event-location/fix-event-locations";

type EditLocationDialogProps = {
  readonly open: boolean;
  readonly close: () => void;
  readonly location?: EventLocationDto;
  readonly setLocation: (_: EventLocationDto) => void;
};

export type EventLocationOptionType = {
  inputValue?: string;
  name: string;
  street?: string;
  city?: string;
  additionalInformation?: string;
  guid?: string;
};

export const EditLocationDialog = (props: EditLocationDialogProps) => {
  const [inGrandhotel, setInGrandhotel] = useState(
    props?.location?.name === "Grandhotel Augsburg"
  );
  const [selectedEventLocationOption, setSelectedEventLocationOption] =
    useState<EventLocationOptionType | null>(null);
  const [dialogValue, setDialogValue] =
    useState<Omit<EventLocationDto, "guid">>();
  const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);

  const [eventLocations, setEventLocations] = useState<
    EventLocationOptionType[]
  >([]);

  const [room, setRoom] = useState<string>();

  const handleChangeRoom = (event: SelectChangeEvent) => {
    const selectedEventLocation = fixEventLocations.find(
      (l) => l.additionalInformation === event.target.value
    );
    console.log(selectedEventLocation);
    setRoom(event.target.value as string);
  };

  useEffect(() => {
    eventLocationApi.listEventLocations().then((r) =>
      setEventLocations(
        r.data.eventLocations?.map((v) => {
          const eventLocationOption: EventLocationOptionType = {
            name: v.name ?? "",
            street: v.street ?? undefined,
            city: v.city ?? undefined,
            guid: v.guid ?? undefined,
            additionalInformation: v.additionalInformation ?? undefined,
          };
          return eventLocationOption;
        }) ?? []
      )
    );
  }, []);

  const handleCloseCreateDialog = () => {
    setDialogValue(undefined);
    setIsCreateDialogOpen(false);
  };

  const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    if (!!dialogValue?.name) {
      event.preventDefault();
      eventLocationApi
        .createEventLocation(
          dialogValue.name,
          dialogValue.street ?? undefined,
          dialogValue.city ?? undefined
        )
        .then((r) => {
          const newEventLocation: EventLocationOptionType = {
            name: r.data.name ?? "",
            street: r.data.street ?? undefined,
            city: r.data.city ?? undefined,
            additionalInformation: r.data.additionalInformation ?? undefined,
            guid: r.data.guid ?? undefined,
          };
          setEventLocations((curr) => [...curr, newEventLocation]);
          setSelectedEventLocationOption(newEventLocation);
          handleCloseCreateDialog();
        });
    }
  };

  const onChange = (
    _: React.SyntheticEvent<Element, Event>,
    newValue: string | EventLocationOptionType | null
  ) => {
    if (typeof newValue === "string") {
      // timeout to avoid instant validation of the dialog's form.
      setTimeout(() => {
        setIsCreateDialogOpen(true);
        setDialogValue({
          name: newValue,
        });
      });
    } else if (newValue && newValue.inputValue) {
      setIsCreateDialogOpen(true);
      setDialogValue({
        name: newValue.inputValue,
      });
    } else {
      setSelectedEventLocationOption(newValue);
    }
  };

  return (
    <Dialog open={props.open}>
      <DialogContent>
        <Stack spacing={2}>
          <Stack direction="row" alignItems="center">
            <Typography>Event findet im Grandhotel statt:</Typography>
            <Switch
              checked={inGrandhotel}
              onChange={(e) => setInGrandhotel(e.target.checked)}
            />
          </Stack>
          {inGrandhotel ? (
            <FormControl fullWidth>
              <InputLabel>Room</InputLabel>
              <Select value={room} label="Room" onChange={handleChangeRoom}>
                {fixEventLocations.map((l, i) => (
                  <MenuItem
                    key={i}
                    value={l.additionalInformation ?? undefined}
                  >
                    {l.additionalInformation ?? "<no-room>"}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          ) : (
            <AddEventLocationAutocomplete
              value={selectedEventLocationOption}
              onChange={onChange}
              eventLocations={eventLocations}
            />
          )}

          <AddNewEventLocationDialog
            open={isCreateDialogOpen}
            onClose={handleCloseCreateDialog}
            onSubmit={handleSubmit}
            setDialogValue={setDialogValue}
            dialogValue={dialogValue}
          />
        </Stack>
      </DialogContent>
      <DialogActions>
        <Button onClick={props.close}>Ok</Button>
      </DialogActions>
    </Dialog>
  );
};
