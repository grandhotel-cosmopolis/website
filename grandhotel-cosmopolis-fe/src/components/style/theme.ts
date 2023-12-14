import { createTheme } from "@mui/material";

const theme = createTheme({
  palette: {
    primary: {
      main: "#325432",
    },
    secondary: {
      main: "#EAE3D5",
    },
    text: {
      secondary: "#827453",
    },
  },
  typography: {
    body1: { lineHeight: 1.7, fontSize: 18 },
  },
});

export default theme;
