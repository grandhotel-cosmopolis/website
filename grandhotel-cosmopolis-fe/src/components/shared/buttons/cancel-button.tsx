import { Button } from "@mui/material";
import CancelIcon from "@mui/icons-material/Cancel";

type CancelButtonProps = {
  readonly onClick: () => void;
  readonly isCancelled: boolean;
};

export const CancelButton = (props: CancelButtonProps) => {
  return (
    <Button
      variant={props.isCancelled ? "contained" : "outlined"}
      startIcon={<CancelIcon />}
      onClick={props.onClick}
    >
      Cancel
    </Button>
  );
};
