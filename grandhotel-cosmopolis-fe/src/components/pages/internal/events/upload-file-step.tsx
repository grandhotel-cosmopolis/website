import { useRef, useState } from "react";
import { fileApi } from "../../../../infrastructure/api";
import { FileDto } from "../../../../infrastructure/generated/openapi";
import { Button, CircularProgress, Stack, Typography } from "@mui/material";

type UploadState = "uploading" | "failed" | "finished";
type UploadFileStepProps = {
  readonly finish: () => void;
  readonly setUploadedFile: (_: FileDto) => void;
};

export const UploadFileStep = (props: UploadFileStepProps) => {
  const [uploadState, setUpoloadState] = useState<UploadState>();
  const [file, setFile] = useState<File>();

  const uploadFile = () => {
    setUpoloadState("uploading");
    fileApi
      .uploadFile(file)
      .then((response) => {
        props.setUploadedFile(response.data);
        setUpoloadState("finished");
        props.finish();
      })
      .catch(() => setUpoloadState("failed"));
  };

  return (
    <Stack>
      <UploadFileStepContent
        setFile={setFile}
        file={file}
        state={uploadState}
        uploadFile={uploadFile}
      />
    </Stack>
  );
};

type UploadFileStepContentProps = {
  readonly state?: UploadState;
  readonly file?: File;
  readonly setFile: (_?: File) => void;
  readonly uploadFile: () => void;
};

const UploadFileStepContent = (props: UploadFileStepContentProps) => {
  const inputRef = useRef<HTMLInputElement | null>(null);

  if (props.state === "uploading") {
    return <CircularProgress />;
  }
  if (props.state === "finished") {
    return <Typography>TODO finished das muss noch geschied werden</Typography>;
  }
  if (props.state === "failed") {
    return (
      <Typography>TODO failed das muss auch noch gescheid werden</Typography>
    );
  }
  return (
    <>
      <Button variant="outlined" onClick={() => inputRef.current?.click()}>
        Select File
      </Button>
      <Typography mt={3}>
        {!!props.file ? props.file.name : "Select a file"}
      </Typography>
      {!!props.file && (
        <Button variant="outlined" sx={{ mt: 3 }} onClick={props.uploadFile}>
          Upload
        </Button>
      )}
      <input
        ref={inputRef}
        type="file"
        style={{ display: "none" }}
        onChange={(e) => props.setFile(e.target.files?.item(0) ?? undefined)}
      />
    </>
  );
};
