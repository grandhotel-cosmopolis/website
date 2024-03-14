import ListIcon from "@mui/icons-material/List";
import { Button } from "@mui/material";

type ListButtonProps = {
  readonly active: boolean;
  readonly onClick: () => void;
};

export const ListButton = (props: ListButtonProps) => {
  return (
    <Button
      variant={props.active ? "contained" : "outlined"}
      startIcon={<ListIcon />}
      onClick={props.onClick}
    >
      List
    </Button>
  );
};
