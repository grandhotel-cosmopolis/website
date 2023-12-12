import { Box, Stack, Typography } from "@mui/material";
import Welcome from "../../assets/willkommen.jpg";
import { useIsMobileView } from "../hooks/screen-sizes/use-is-mobile-view";

export const Home = () => {
  const isMobileView = useIsMobileView();
  return (
    <Box width="100%">
      <Box
        width="100%"
        sx={{
          backgroundImage: `linear-gradient(rgba(68,68,68,0.75),rgba(68,68,68,0.75)),url(${Welcome})`,
          backgroundSize: "auto,cover",
          backgroundRepeat: "no-repeat",
          backgroundPosition: "70% 50%",
        }}
      >
        <Stack alignItems="center" p={isMobileView ? 5 : 10}>
          <Typography color="white" variant={isMobileView ? "h4" : "h2"}>
            Willkommen!
          </Typography>
          <Typography
            color="white"
            variant={isMobileView ? "body1" : "h6"}
            maxWidth={500}
            textAlign="center"
          >
            Ein leerstehendes Altenheim in der Augsburger Altstadt wird zur
            Verhandlungszone für die Anerkennung einer kosmopolitischen
            Wirklichkeit in unserer Gesellschaft. Was anfangs eine kühne Idee
            war, wächst tagtäglich und wirkt weit über die Hausmauern hinaus.
          </Typography>
        </Stack>
      </Box>
    </Box>
  );
};
