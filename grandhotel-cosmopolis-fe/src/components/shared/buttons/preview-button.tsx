import { Button } from "@mui/material";
import Visibility from "@mui/icons-material/Visibility";
import VisibilityOff from "@mui/icons-material/VisibilityOff";

type PreviewButtonProps = {
  readonly active: boolean;
  readonly onClick: () => void;
};

export const PreviewButton = (props: PreviewButtonProps) => {
  return (
    <Button
      variant={props.active ? "contained" : "outlined"}
      startIcon={props.active ? <VisibilityOff /> : <Visibility />}
      onClick={props.onClick}
    >
      Preview
    </Button>
  );
};
