import {
  Dialog,
  DialogContent,
  DialogActions,
  Button,
  Typography,
  Stack,
  Box,
} from "@mui/material";
import { FileDto } from "../../../../../../infrastructure/generated/openapi";
import { FileUploadButton } from "../../../../../shared/buttons/file-upload-button";
import { useState } from "react";
import { fileApi } from "../../../../../../infrastructure/api";

type EditImageDialogProps = {
  readonly open: boolean;
  readonly close: () => void;
  readonly image?: FileDto;
  readonly setImage: (_?: FileDto) => void;
};

export const EditImageDialog = (props: EditImageDialogProps) => {
  const [isUploading, setIsUploading] = useState(false);

  const uploadFile = (file: File) => {
    setIsUploading(true);
    fileApi
      .uploadFile(file)
      .then((response) => {
        props.setImage(response.data);
        setIsUploading(false);
      })
      .catch(() => setIsUploading(false));
  };

  return (
    <Dialog open={props.open}>
      <DialogContent>
        {!!props.image && (
          <>
            <Typography>Current image:</Typography>
            <img
              style={{
                width: "100%",
              }}
              src={props.image?.fileUrl}
            />
          </>
        )}
        <Stack alignItems="center">
          <Box>
            {!!props.image && <Typography>Change Image:</Typography>}
            <FileUploadButton isUploading={isUploading} onUpload={uploadFile} />
          </Box>
        </Stack>
      </DialogContent>
      <DialogActions>
        <Button onClick={props.close}>Ok</Button>
      </DialogActions>
    </Dialog>
  );
};
