import { Box, Stack, Typography, useTheme } from "@mui/material";
import { ElementWrapper } from "../element-wrapper";
import { Logo } from "../../../assets/general/logo";
import { useIsTabletView } from "../../hooks/screen-sizes/use-is-tablet-view";
import { useLocation, useNavigate } from "react-router-dom";

export const TopBarNavigation = () => {
  const theme = useTheme();
  const isTabletView = useIsTabletView();
  const navigate = useNavigate();
  return (
    <Box
      sx={(theme) => ({
        backgroundColor: theme.palette.secondary.main,
        flexGrow: 1,
      })}
      position="sticky"
      top={0}
      pt={1}
      zIndex={1000}
    >
      <ElementWrapper dense>
        <Box
          width="100%"
          sx={{ backgroundColor: "#fff4f4" }}
          borderBottom={`12px solid ${theme.palette.secondary.main}`}
        >
          {!isTabletView ? (
            <Stack direction="row" spacing={2}>
              <Box
                pl={2}
                pt={1}
                onClick={() => navigate("/")}
                sx={{ "&:hover": { cursor: "pointer" } }}
              >
                <Logo size="small" color="green" />
              </Box>
              <Stack direction="row" width="100%">
                <MenuItem text="Home" link="/" />
                <MenuItem text="Events" link="/events" />
                <MenuItem text="Asylum & Political Insights" link="/asylum" />
                <MenuItem text="Cafe" link="/cafe" />
                <MenuItem text="Space" link="/space" />
                <MenuItem text="About us" link="/about" />
                <MenuItem text="Contact" link="/contact" />
              </Stack>
            </Stack>
          ) : (
            <Typography>TODO</Typography>
          )}
        </Box>
      </ElementWrapper>
    </Box>
  );
};

type MenuItemProps = {
  readonly text: string;
  readonly link: string;
};

const MenuItem = (props: MenuItemProps) => {
  const theme = useTheme();
  const navigate = useNavigate();
  const location = useLocation();
  return (
    <Box
      flexGrow={1}
      sx={{
        "&:hover": {
          backgroundColor: theme.palette.primary.dark,
          cursor: "pointer",
        },
      }}
      justifyContent="center"
      alignItems="center"
      alignContent="center"
      display="flex"
      borderBottom={
        props.link === location.pathname
          ? `2px solid ${theme.palette.primary.main}`
          : undefined
      }
      onClick={() => navigate(props.link)}
      pt={props.link === location.pathname ? "2px" : 0}
    >
      <Box
        width="100%"
        height="100%"
        color={
          props.link === location.pathname
            ? theme.palette.primary.main
            : theme.palette.text.secondary
        }
        sx={{ "&:hover": { color: "white" } }}
        display="flex"
        justifyContent="center"
        alignItems="center"
      >
        <Typography color="inherit" textTransform="uppercase">
          {props.text}
        </Typography>
      </Box>
    </Box>
  );
};
