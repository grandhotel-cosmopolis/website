import { Box, Stack, Typography } from "@mui/material";
import Welcome from "../../assets/willkommen.jpg";
import { useIsMobileView } from "../hooks/screen-sizes/use-is-mobile-view";
import Image from "../../assets/image.jpg";
import Anniversary from "../../assets/anniversary.jpg";
import { HeaderImageBodyCard } from "../shared/header-image-body-card/header-image-body-card";

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
      <HeaderImageBodyCard
        body="Wir haben viele Reaktionen, aus verschiedenen Perspektiven, zu unserem „offenen Aufruf für Kunstausstellung - Friedensstadt“ oder zu unserer „Nichtpositionierung“ bekommen.
Wir wollen keine Angriffsfläche sein, sondern Raum für Wärme und Menschlichkeit bieten. Wir wollen unsere Communities in dieser überrollenden Zeit auffangen und Emotionen, Wünschen und Bedürfnissen zuhören – eine Kunstausstellung kuratieren, die Allen von Krieg und Unterdrückung betroffenen Menschen und Communities Raum für ihren Schmerz, ihre Trauer und Wut bietet.
Wir arbeiten im Kollektiv mit Menschen unterschiedlicher Backgrounds, Erfahrungswerte und Sozialisation, weshalb unsere Arbeit auf Diskussion und Gespräch fußt – und vor allem auf Anerkennung individueller Realitäten und Emotionen.

Wir wollen uns nicht gegenseitig verlieren, denn wir brauchen uns mehr denn je."
        image={Image}
        title="Wir brauchen uns jetzt mehr denn je"
      />
      <HeaderImageBodyCard
        body="Zeit zu feiern!
       Wir möchten euch einladen, mit uns das Jubiläum des GrandHotel Cosmopolis zu feiern! Es waren keine einfachen 10 Jahre, mit Höhen und Tiefen. Deshalb ist es für uns alle wichtig, dass wir uns die Zeit nehmen, den Erfolg des GrandHotels zu feiern, aus seiner Geschichte zu lernen und ein gemeinsames Wochenende mit unseren tollen Freund*innen und Partner*innen zu genießen. "
        image={Anniversary}
        title="Wir feiern 10+ Jahre Gemeinschaft, Vielfalt und Solidarität!"
      />
    </Box>
  );
};
