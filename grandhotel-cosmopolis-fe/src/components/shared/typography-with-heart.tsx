import { Stack, Typography, useTheme } from "@mui/material";
import FavoriteIcon from "@mui/icons-material/Favorite";

type TypographyWithHeartProps = {
  readonly text?: string;
};

export const TypographyWithHeart = (props: TypographyWithHeartProps) => {
  const theme = useTheme();
  return (
    <Stack direction="row">
      <FavoriteIcon htmlColor={theme.palette.primary.main} />
      <Typography pl={2} pt={0.2}>
        {props.text}
      </Typography>
    </Stack>
  );
};
