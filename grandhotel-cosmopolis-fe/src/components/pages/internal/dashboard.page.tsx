import { Button } from "@mui/material";
import { useState } from "react";
import { CreateEventDialog } from "./events/create-event-dialog";

export const Dashboard = () => {
  const [createEvent, setCreateEvent] = useState(false);
  return (
    <>
      <CreateEventDialog
        open={createEvent}
        close={() => setCreateEvent(false)}
      />
      <Button variant="contained" onClick={() => setCreateEvent(true)}>
        Create new Event
      </Button>
    </>
  );
};
