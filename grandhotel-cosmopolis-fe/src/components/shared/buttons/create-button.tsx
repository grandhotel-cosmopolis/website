import { Button } from "@mui/material";
import AddIcon from "@mui/icons-material/Add";

type CreateButtonProps = {
  readonly onClick: () => void;
};

export const CreateButton = (props: CreateButtonProps) => {
  return (
    <Button
      variant={"outlined"}
      startIcon={<AddIcon />}
      onClick={props.onClick}
    >
      Add
    </Button>
  );
};
