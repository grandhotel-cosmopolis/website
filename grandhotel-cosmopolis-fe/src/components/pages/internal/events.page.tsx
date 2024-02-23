import { Box, Stack, Tab, Tabs } from "@mui/material";
import { useState } from "react";
import { SingleEventsTab } from "./events/single-event-tab/single-events-tab";
import { RecurringEventsTab } from "./events/recurring-event-tab/recurring-events-tab";
import { SingleEventDetailsDialog } from "./events/single-event-details-dialog/single-event-details-dialog";
import { CreateButton } from "../../shared/buttons/create-button";

type Tabs = "SingleEvents" | "RecurringEvents";

export const Events = () => {
  const [selectedTab, setSelectedTab] = useState<Tabs>("SingleEvents");
  const [isCreateSingleEventDialogOpen, setIsCreateSingleEventDialogOpen] =
    useState(false);

  const handleChange = (_: React.SyntheticEvent, newValue: string) => {
    setSelectedTab(newValue as Tabs);
  };

  return (
    <>
      <Stack direction="row" justifyContent="space-between">
        <Box sx={{ bgcolor: "background.paper", mb: 2 }}>
          <Tabs value={selectedTab} onChange={handleChange}>
            <Tab label="Single Events" value={"SingleEvents"} />
            <Tab label="Recurring Events" value={"RecurringEvents"} />
          </Tabs>
        </Box>
        <Box sx={{ mr: 1 }}>
          <CreateButton
            onClick={() => setIsCreateSingleEventDialogOpen(true)}
          />
        </Box>
      </Stack>
      <SingleEventDetailsDialog
        open={isCreateSingleEventDialogOpen}
        closeDialog={() => setIsCreateSingleEventDialogOpen(false)}
        mode="Create"
      />
      {selectedTab === "SingleEvents" && <SingleEventsTab />}
      {selectedTab === "RecurringEvents" && <RecurringEventsTab />}
    </>
  );
};
