import { Stack, Typography, Link } from "@mui/material";
import PhoneIcon from "@mui/icons-material/Phone";
import EmailIcon from "@mui/icons-material/Email";
import { useIsTabletView } from "../../hooks/screen-sizes/use-is-tablet-view";

export const Contact = () => {
  const isTabletView = useIsTabletView();
  return (
    <Stack
      spacing={isTabletView ? 0 : 2}
      direction={isTabletView ? "column" : "row"}
      p={1}
      alignItems="center"
    >
      <Link
        color="inherit"
        sx={{ textDecoration: "none" }}
        href="TEL:+49 821 450 82 411"
      >
        <Stack
          direction={isTabletView ? "row" : "column"}
          spacing={isTabletView ? 1 : 0}
        >
          <Stack alignSelf="center">
            <PhoneIcon />
          </Stack>
          <Typography>+49 821 450 82 411</Typography>
        </Stack>
      </Link>
      <Link
        sx={{ textDecoration: "none" }}
        color="inherit"
        href="MAILTO:willkommen@grandhotel-cosmopolis.org"
      >
        <Stack
          direction={isTabletView ? "row" : "column"}
          spacing={isTabletView ? 1 : 0}
        >
          <Stack alignSelf="center">
            <EmailIcon />
          </Stack>
          <Typography>willkommen@grandhotel-cosmopolis.org</Typography>
        </Stack>
      </Link>
    </Stack>
  );
};
