import { Link, Stack, Typography } from "@mui/material";
import EmailIcon from "@mui/icons-material/Email";

type MailProps = {
  readonly align: "row" | "column";
};

export const Mail = (props: MailProps) => {
  return (
    <Link
      sx={{ textDecoration: "none" }}
      color="inherit"
      href="MAILTO:willkommen@grandhotel-cosmopolis.org"
    >
      <Stack direction={props.align} spacing={props.align === "row" ? 1 : 0}>
        <Stack alignSelf="center">
          <EmailIcon />
        </Stack>
        <Typography textAlign="center">
          willkommen@grandhotel-cosmopolis.org
        </Typography>
      </Stack>
    </Link>
  );
};
