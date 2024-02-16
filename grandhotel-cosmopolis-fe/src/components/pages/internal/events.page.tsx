import { Box, Tab, Tabs } from "@mui/material";
import { useState } from "react";
import { SingleEventsTab } from "./events/single-events-tab";
import { RecurringEventsTab } from "./events/recurring-events-tab";

type Tabs = "SingleEvents" | "RecurringEvents";

export const Events = () => {
  const [selectedTab, setSelectedTab] = useState<Tabs>("SingleEvents");

  const handleChange = (event: React.SyntheticEvent, newValue: string) => {
    setSelectedTab(newValue as Tabs);
  };

  return (
    <>
      <Box sx={{ bgcolor: "background.paper", mb: 2 }}>
        <Tabs value={selectedTab} onChange={handleChange}>
          <Tab label="Single Events" value={"SingleEvents"} />
          <Tab label="Recurring Events" value={"RecurringEvents"} />
        </Tabs>
      </Box>
      {selectedTab === "SingleEvents" && <SingleEventsTab />}
      {selectedTab === "RecurringEvents" && <RecurringEventsTab />}
    </>
  );
};
