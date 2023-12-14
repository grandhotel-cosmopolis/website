import { Divider, Grid, Stack, Typography, useTheme } from "@mui/material";
import { ElementWrapper } from "../element-wrapper";
import { SocialLinks } from "../social-links";
import { Logo } from "../../../assets/general/logo";
import { Mail } from "../mail";
import { useIsTabletView } from "../../hooks/screen-sizes/use-is-tablet-view";
import { Phone } from "../phone";
import { useTranslation } from "react-i18next";

export const Footer = () => {
  const theme = useTheme();
  const isTabletView = useIsTabletView();
  const { t } = useTranslation();
  return (
    <>
      <Divider />
      <ElementWrapper fullWidthBackgroundColor={theme.palette.primary.main}>
        <Grid container spacing={2}>
          <Grid
            item
            sm={12}
            md={4}
            sx={{ display: "flex", justifyContent: "center" }}
          >
            <Stack justifyContent="center">
              <Logo size="small" />
            </Stack>
          </Grid>
          <Grid
            item
            sm={12}
            md={4}
            sx={{ display: "flex", justifyContent: "center", color: "white" }}
          >
            <Stack alignItems="center" justifyContent="center">
              <Typography sx={{ fontStyle: "italic" }}>
                {t("footer.cafe.openingHours")}
              </Typography>
              <Typography
                variant="body2"
                textAlign="center"
                sx={{ fontStyle: "italic", pt: 2, whiteSpace: "pre-line" }}
              >
                {t("footer.cafe.tuesdayToSaturday")}
              </Typography>
              <Typography
                variant="body2"
                textAlign="center"
                sx={{ fontStyle: "italic", pt: 2, whiteSpace: "pre-line" }}
              >
                {t("footer.cafe.sundayToMonday")}
              </Typography>
            </Stack>
          </Grid>
          <Grid
            item
            sm={12}
            md={4}
            sx={{ display: "flex", justifyContent: "center", color: "white" }}
          >
            <Stack alignItems="center" justifyContent="center">
              <Typography>{t("footer.imprint")}</Typography>
              <Typography>{t("footer.privacy")}</Typography>
              <Typography>{t("footer.donate")}</Typography>
            </Stack>
          </Grid>
          <Grid
            item
            sm={12}
            md={4}
            sx={{ display: "flex", justifyContent: "center", color: "white" }}
          >
            <Stack justifyContent="center">
              <Phone align={isTabletView ? "row" : "column"} />
            </Stack>
          </Grid>
          <Grid
            item
            sm={12}
            md={4}
            sx={{ display: "flex", justifyContent: "center", color: "white" }}
          >
            <Stack justifyContent="center">
              <Mail align={isTabletView ? "row" : "column"} />
            </Stack>
          </Grid>
          <Grid
            item
            sm={12}
            md={4}
            sx={{ display: "flex", justifyContent: "center", color: "white" }}
          >
            <Stack justifyContent="center">
              <SocialLinks />
            </Stack>
          </Grid>
        </Grid>
      </ElementWrapper>
    </>
  );
};
