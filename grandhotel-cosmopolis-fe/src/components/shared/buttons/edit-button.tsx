import { Button } from "@mui/material";
import EditOff from "@mui/icons-material/EditOff";
import Edit from "@mui/icons-material/Edit";

type EditButtonProps = {
  readonly active: boolean;
  readonly onClick: () => void;
};

export const EditButton = (props: EditButtonProps) => {
  return (
    <Button
      variant={props.active ? "contained" : "outlined"}
      startIcon={props.active ? <EditOff /> : <Edit />}
      onClick={props.onClick}
    >
      Edit
    </Button>
  );
};
