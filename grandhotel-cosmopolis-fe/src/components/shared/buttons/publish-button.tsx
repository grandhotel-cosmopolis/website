import { Button } from "@mui/material";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import UnpublishedIcon from "@mui/icons-material/Unpublished";

type PublishButtonProps = {
  readonly published: boolean;
  readonly onClick: () => void;
};

export const PublishButton = (props: PublishButtonProps) => {
  return (
    <Button
      variant={props.published ? "contained" : "outlined"}
      startIcon={props.published ? <CheckCircleIcon /> : <UnpublishedIcon />}
      onClick={props.onClick}
    >
      Public
    </Button>
  );
};
