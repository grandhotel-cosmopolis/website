import { Box, Tab, Tabs, Typography } from "@mui/material";
import { useState } from "react";
import { UserManagementTab } from "./administration/user-management-tab";
import { DevToolsTab } from "./administration/dev-tools-tab";

type Tabs = "UserManagement" | "DevTools";

export const Administration = () => {
  const [selectedTab, setSelectedTab] = useState<Tabs>("UserManagement");

  const handleChange = (event: React.SyntheticEvent, newValue: string) => {
    setSelectedTab(newValue as Tabs);
  };

  return (
    <>
      <Box sx={{ bgcolor: "background.paper", mb: 2 }}>
        <Tabs value={selectedTab} onChange={handleChange}>
          <Tab label="User management" value={"UserManagement"} />
          <Tab label="Dev tools" value={"DevTools"} />
        </Tabs>
      </Box>
      {selectedTab === "UserManagement" && <UserManagementTab />}
      {selectedTab === "DevTools" && <DevToolsTab />}
    </>
  );
};
