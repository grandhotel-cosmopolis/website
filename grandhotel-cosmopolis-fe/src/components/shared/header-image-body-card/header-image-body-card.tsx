import { Stack, Typography, Grid, Box } from "@mui/material";
import { useIsMobileView } from "../../hooks/screen-sizes/use-is-mobile-view";
import { useIsTabletView } from "../../hooks/screen-sizes/use-is-tablet-view";

type HeaderImageBodyCardProps = {
  readonly title: string;
  readonly image: string;
  readonly body: string;
};

export const HeaderImageBodyCard = (props: HeaderImageBodyCardProps) => {
  const isMobileView = useIsMobileView();
  const isTabletView = useIsTabletView();
  return (
    <Box
      display="flex"
      justifyContent="center"
      alignItems="center"
      width="100%"
      px={5}
    >
      <Box
        sx={(theme) => ({
          maxWidth: theme.spacing(140),
        })}
        width="100%"
      >
        <Stack
          mt={10}
          alignItems="center"
          p={2}
          sx={(theme) => ({ backgroundColor: theme.palette.primary.main })}
        >
          <Typography
            color="white"
            variant={isMobileView ? "body1" : isTabletView ? "h6" : "h4"}
          >
            {props.title}
          </Typography>
        </Stack>
        <Grid container spacing={5} mt={1}>
          <Grid item xs={12} md={6}>
            <img src={props.image} width="100%" />
          </Grid>
          <Grid item xs={12} md={6}>
            <Typography variant="body1">{props.body}</Typography>
          </Grid>
        </Grid>
      </Box>
    </Box>
  );
};
