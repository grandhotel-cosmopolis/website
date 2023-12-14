import { Box } from "@mui/material";
import { Header } from "./home/header";
import { Change } from "./home/change";
import { Donate } from "./home/donate";
import { Hotel } from "./home/hotel";
import { News } from "./home/news";
import { Together } from "./home/together";

export const Home = () => {
  return (
    <Box width="100%">
      <Header />
      <News />
      <Hotel />
      <Change />
      <Together />
      <Donate />
    </Box>
  );
};
