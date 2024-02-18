import {
  Autocomplete,
  FilterOptionsState,
  TextField,
  createFilterOptions,
} from "@mui/material";
import { EventLocationOptionType } from "../edit-location-dialog";

const filter = createFilterOptions<EventLocationOptionType>();

type AddNewEventLocationAutocompleteProps = {
  readonly value: EventLocationOptionType | null;
  readonly onChange: (
    _: React.SyntheticEvent<Element, Event>,
    newValue: string | EventLocationOptionType | null
  ) => void;
  readonly eventLocations: EventLocationOptionType[];
};

export const AddEventLocationAutocomplete = (
  props: AddNewEventLocationAutocompleteProps
) => {
  const getOptionLabel = (option: string | EventLocationOptionType) => {
    console.log("hell");
    if (typeof option === "string") {
      console.log("was weiß ich");
      return option;
    }
    if (option.inputValue) {
      console.log("was weiß ich2");

      return option.inputValue;
    }
    console.log("hello");
    return getEventLocationDisplayText(option);
  };

  const getEventLocationDisplayText = (e: EventLocationOptionType) => {
    let displayText = e.name;
    displayText += !!e.street ? ` - ${e.street}` : "";
    displayText += !!e.city ? ` - ${e.city}` : "";
    displayText += !!e.additionalInformation
      ? `  - ${e.additionalInformation}`
      : "";
    return displayText;
  };

  const fiterOptions = (
    options: EventLocationOptionType[],
    params: FilterOptionsState<EventLocationOptionType>
  ) => {
    const filtered = filter(options, params);

    if (params.inputValue !== "") {
      filtered.push({
        inputValue: params.inputValue,
        name: `Add "${params.inputValue}"`,
      });
    }

    return filtered;
  };

  return (
    <Autocomplete
      value={props.value}
      onChange={props.onChange}
      filterOptions={fiterOptions}
      options={props.eventLocations}
      getOptionLabel={getOptionLabel}
      selectOnFocus
      clearOnBlur
      handleHomeEndKeys
      renderOption={(props, option) => (
        <li {...props}>{getEventLocationDisplayText(option)}</li>
      )}
      freeSolo
      renderInput={(params) => (
        <TextField {...params} label="Select Event Location" />
      )}
    />
  );
};
