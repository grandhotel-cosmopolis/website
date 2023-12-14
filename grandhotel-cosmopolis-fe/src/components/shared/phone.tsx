import { Link, Stack, Typography } from "@mui/material";
import PhoneIcon from "@mui/icons-material/Phone";

type PhoneProps = {
  readonly align: "row" | "column";
};

export const Phone = (props: PhoneProps) => {
  return (
    <Link
      color="inherit"
      sx={{ textDecoration: "none" }}
      href="TEL:+49 821 450 82 411"
    >
      <Stack direction={props.align} spacing={props.align === "row" ? 1 : 0}>
        <Stack alignSelf="center">
          <PhoneIcon />
        </Stack>
        <Typography>+49 821 450 82 411</Typography>
      </Stack>
    </Link>
  );
};
