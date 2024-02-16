import {
  AppBar,
  Box,
  Drawer,
  IconButton,
  List,
  ListItem,
  ListItemButton,
  ListItemIcon,
  ListItemText,
  Toolbar,
  Typography,
} from "@mui/material";
import {
  SvgIconComponent,
  Dashboard,
  ManageAccounts,
  CalendarMonth,
  Menu,
} from "@mui/icons-material";
import { Permissions } from "../../../infrastructure/generated/openapi";
import { Outlet, useNavigate } from "react-router-dom";
import { Logo } from "../../../assets/general/logo";
import { useEffect, useRef } from "react";

const drawerWidth = 240;

type SideBarNavigationItem = {
  readonly title: string;
  readonly icon: SvgIconComponent;
  readonly navigation: string;
  readonly requiredPermissions: Permissions[];
};

const navigationItems: SideBarNavigationItem[] = [
  {
    title: "Dashboard",
    icon: Dashboard,
    navigation: "/internal",
    requiredPermissions: [],
  },
  {
    title: "Administration",
    icon: ManageAccounts,
    navigation: "/internal/user-management",
    requiredPermissions: [],
  },
  {
    title: "Events",
    icon: CalendarMonth,
    navigation: "/internal/events",
    requiredPermissions: [],
  },
];

export const InternalBasePage = () => {
  const navigate = useNavigate();

  return (
    <Box sx={{ display: "flex" }}>
      <AppBar
        position="fixed"
        sx={{ zIndex: (theme) => theme.zIndex.drawer + 1 }}
      >
        <Toolbar>
          <IconButton color="inherit">
            <Menu />
          </IconButton>
          <IconButton sx={{ ml: 2 }} onClick={() => navigate("/internal")}>
            <Logo size="tiny" />
          </IconButton>
        </Toolbar>
      </AppBar>
      <Drawer
        variant="permanent"
        sx={{
          width: drawerWidth,
          flexShrink: 0,
          [`& .MuiDrawer-paper`]: {
            width: drawerWidth,
            boxSizing: "border-box",
          },
        }}
      >
        <Toolbar />
        <Box sx={{ overflow: "auto" }}>
          <List>
            {navigationItems.map((item, index) => (
              <ListItem
                key={index}
                disablePadding
                onClick={() => navigate(item.navigation)}
              >
                <ListItemButton selected>
                  <ListItemIcon>
                    <item.icon />
                  </ListItemIcon>
                  <ListItemText primary={item.title} />
                </ListItemButton>
              </ListItem>
            ))}
          </List>
        </Box>
      </Drawer>
      <Box
        sx={{
          height: "100vh",
          p: 3,
          display: "flex",
          flexDirection: "column",
          overflow: "hidden",
        }}
      >
        <Toolbar />
        <Outlet />
      </Box>
    </Box>
  );
};
