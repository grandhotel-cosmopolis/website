import { Fragment, useEffect, useState } from "react";
import { EventLocationDto } from "../../../../infrastructure/generated/openapi";
import { eventLocationApi } from "../../../../infrastructure/api";
import { AddNewEventLocationAutocomplete } from "./event-location/add-new-event-location-autocomplete";
import { AddNewEventLocationDialog } from "./event-location/add-new-event-location-dialog";
import { Button, Stack } from "@mui/material";

export type EventLocationOptionType = {
  inputValue?: string;
  name: string;
  street?: string;
  city?: string;
  guid?: string;
};

type EventLocationStepProps = {
  readonly finish: () => void;
  readonly setSelectedEventLocation: (_: EventLocationDto) => void;
};

export const EventLocationStep = (props: EventLocationStepProps) => {
  const [value, setValue] = useState<EventLocationOptionType | null>(null);
  const [open, toggleOpen] = useState(false);
  const [dialogValue, setDialogValue] =
    useState<Omit<EventLocationDto, "guid">>();

  const [eventLocations, setEventLocations] = useState<
    EventLocationOptionType[]
  >([]);
  useEffect(() => {
    eventLocationApi.listEventLocations().then((r) =>
      setEventLocations(
        r.data.eventLocations?.map((v) => {
          const eventLocationOption: EventLocationOptionType = {
            name: v.name ?? "",
            street: v.street ?? undefined,
            city: v.city ?? undefined,
            guid: v.guid ?? undefined,
          };
          return eventLocationOption;
        }) ?? []
      )
    );
  }, []);

  const handleClose = () => {
    setDialogValue(undefined);
    toggleOpen(false);
  };

  const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    if (!!dialogValue?.name) {
      event.preventDefault();
      eventLocationApi
        .addEventLocation(
          dialogValue.name,
          dialogValue.street ?? undefined,
          dialogValue.city ?? undefined
        )
        .then((r) => {
          const newEventLocation: EventLocationOptionType = {
            name: r.data.name ?? "",
            street: r.data.street ?? undefined,
            city: r.data.city ?? undefined,
            guid: r.data.guid ?? undefined,
          };
          setEventLocations((curr) => [...curr, newEventLocation]);
          setValue(newEventLocation);
          handleClose();
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
        toggleOpen(true);
        setDialogValue({
          name: newValue,
        });
      });
    } else if (newValue && newValue.inputValue) {
      toggleOpen(true);
      setDialogValue({
        name: newValue.inputValue,
      });
    } else {
      setValue(newValue);
    }
  };

  return (
    <Fragment>
      <Stack spacing={2}>
        <AddNewEventLocationAutocomplete
          value={value}
          onChange={onChange}
          eventLocations={eventLocations}
        />
        <Button
          disabled={!value}
          variant="outlined"
          onClick={() => {
            const eventLocation: EventLocationDto = {
              name: value?.name,
              street: value?.street,
              city: value?.city,
              guid: value?.guid,
            };
            props.setSelectedEventLocation(eventLocation);
            props.finish();
          }}
        >
          Next
        </Button>
      </Stack>
      <AddNewEventLocationDialog
        open={open}
        onClose={handleClose}
        onSubmit={handleSubmit}
        setDialogValue={setDialogValue}
        dialogValue={dialogValue}
      />
    </Fragment>
  );
};
