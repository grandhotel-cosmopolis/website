import { Box } from "@mui/material";
import { Header } from "./home/header";
import { Change } from "./home/change";
import { Donate } from "./home/donate";
import { Hotel } from "./home/hotel";
import { News } from "./home/news";
import { Together } from "./home/together";
import { UpcomingEvents } from "./home/upcoming-events";

export const Home = () => {
  return (
    <Box width="100%">
      <Header />
      <UpcomingEvents />
      <News />
      <Hotel />
      <Change />
      <Together />
      <Donate />
    </Box>
  );
};
