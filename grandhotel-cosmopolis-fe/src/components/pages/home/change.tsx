import {
  Stack,
  Grid,
  Typography,
  Button,
  Card,
  CardContent,
  useTheme,
} from "@mui/material";
import { ElementWrapper } from "../../shared/element-wrapper";
import { TypographyWithHeart } from "../../shared/typography-with-heart";
import { useTranslation } from "react-i18next";
import { useIsMobileView } from "../../hooks/screen-sizes/use-is-mobile-view";

export const Change = () => {
  const theme = useTheme();
  const { t } = useTranslation();
  const isMobileView = useIsMobileView();
  return (
    <ElementWrapper fullWidthBackgroundColor={theme.palette.secondary.main}>
      <Stack>
        <Grid container spacing={5}>
          <Grid item xs={12} md={6}>
            <Stack spacing={3}>
              <Typography
                variant={isMobileView ? "h6" : "h5"}
                sx={(theme) => ({ color: theme.palette.text.secondary })}
              >
                {t("home.change.title")}
              </Typography>
              <Typography variant={isMobileView ? "body2" : "body1"}>
                {t("home.change.body")}
              </Typography>
              <Button variant="contained">{t("home.change.contribute")}</Button>
            </Stack>
          </Grid>
          <Grid
            item
            xs={12}
            md={6}
            sx={{ display: "flex", justifyContent: "center" }}
          >
            <Card sx={{ display: "flex", alignItems: "center", width: "100%" }}>
              <CardContent sx={{ px: 8 }}>
                <Stack spacing={2}>
                  <TypographyWithHeart text={t("home.change.community")} />
                  <TypographyWithHeart text={t("home.change.solidarity")} />
                  <TypographyWithHeart text={t("home.change.projects")} />
                  <TypographyWithHeart text={t("home.change.networks")} />
                  <TypographyWithHeart text={t("home.change.diversity")} />
                </Stack>
              </CardContent>
            </Card>
          </Grid>
        </Grid>
      </Stack>
    </ElementWrapper>
  );
};
