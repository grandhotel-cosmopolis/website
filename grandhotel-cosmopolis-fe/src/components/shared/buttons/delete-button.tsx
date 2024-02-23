import { Button } from "@mui/material";
import DeleteIcon from "@mui/icons-material/Delete";

type DeleteButtonProps = {
  readonly onClick: () => void;
};

export const DeleteButton = (props: DeleteButtonProps) => {
  return (
    <Button
      variant={"outlined"}
      startIcon={<DeleteIcon />}
      onClick={props.onClick}
    >
      Delete
    </Button>
  );
};
