import { Box, IconButton } from "@mui/material";
import InstagramIcon from "@mui/icons-material/Instagram";
import FacebookIcon from "@mui/icons-material/Facebook";

export const SocialLinks = () => {
  return (
    <Box height="100%" display="flex" alignSelf="center">
      <IconButton
        color="inherit"
        href="https://www.facebook.com/grandhotelcosmopolis/"
        target="_blank"
        rel="noopener noreferer"
      >
        <FacebookIcon />
      </IconButton>
      <IconButton
        color="inherit"
        href="https://www.instagram.com/grandhotel_cosmopolis/"
        target="_blank"
        rel="noopener noreferer"
      >
        <InstagramIcon />
      </IconButton>
    </Box>
  );
};
