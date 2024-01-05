import { AppBar, Box, Stack, Toolbar } from "@mui/material";
import { SocialLinks } from "../social-links";
import { Contact } from "../contact";
import { useIsTabletView } from "../../hooks/screen-sizes/use-is-tablet-view";

export const Header = () => {
  const isTabletView = useIsTabletView();

  return (
    <Box>
      <AppBar position="static">
        <Toolbar>
          <Box
            display="flex"
            alignItems="center"
            width="100%"
            justifyContent="center"
          >
            <Stack
              width="100%"
              sx={(theme) => ({ maxWidth: theme.spacing(140) })}
              direction={isTabletView ? "column" : "row"}
              justifyContent="space-between"
            >
              <SocialLinks />
              <Contact />
            </Stack>
          </Box>
        </Toolbar>
      </AppBar>
    </Box>
  );
};
