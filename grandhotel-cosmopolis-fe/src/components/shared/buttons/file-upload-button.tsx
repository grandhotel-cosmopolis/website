import { styled } from "@mui/material/styles";
import CloudUploadIcon from "@mui/icons-material/CloudUpload";
import LoadingButton from "@mui/lab/LoadingButton";

const VisuallyHiddenInput = styled("input")({
  clip: "rect(0 0 0 0)",
  clipPath: "inset(50%)",
  height: 1,
  overflow: "hidden",
  position: "absolute",
  bottom: 0,
  left: 0,
  whiteSpace: "nowrap",
  width: 1,
});

type FileUploadButtonProps = {
  readonly onUpload: (_: File) => void;
  readonly isUploading: boolean;
};

export const FileUploadButton = (props: FileUploadButtonProps) => {
  return (
    <LoadingButton
      loading={props.isUploading}
      component="label"
      role={undefined}
      variant="contained"
      tabIndex={-1}
      startIcon={<CloudUploadIcon />}
    >
      Upload file
      <VisuallyHiddenInput
        type="file"
        accept=".png,.jpeg,.jpg"
        onChange={(e) => {
          const file = e.target.files?.item(0);
          if (file) {
            props.onUpload(file);
          }
        }}
      />
    </LoadingButton>
  );
};
