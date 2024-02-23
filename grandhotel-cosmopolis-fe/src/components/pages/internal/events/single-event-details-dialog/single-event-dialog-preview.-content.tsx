import { Box, Button, Stack } from "@mui/material";
import { SingleEventDto } from "../../../../../infrastructure/generated/openapi";
import { SingleEventView } from "../../../../shared/events/single-events/single-event-view";
import { useState } from "react";

type ViewType = "mobile" | "desktop";

type SingleEventDialogPreviewContentProps = {
  readonly singleEvent?: SingleEventDto;
};

export const SingleEventDialogPreviewContent = (
  props: SingleEventDialogPreviewContentProps
) => {
  const [view, setView] = useState<ViewType>("desktop");
  return (
    <Stack alignItems="center">
      <Stack direction="row" spacing={4}>
        <Button
          variant={view === "desktop" ? "contained" : "outlined"}
          onClick={() => setView("desktop")}
        >
          Desktop
        </Button>
        <Button
          variant={view === "mobile" ? "contained" : "outlined"}
          onClick={() => setView("mobile")}
        >
          Mobile
        </Button>
      </Stack>
      <Box
        mt={2}
        maxWidth={view === "mobile" ? 350 : undefined}
        border="1px solid black"
        borderRadius="2%"
      >
        <SingleEventView
          singleEvent={props.singleEvent}
          isMobileView={view === "mobile"}
        />
      </Box>
    </Stack>
  );
};
