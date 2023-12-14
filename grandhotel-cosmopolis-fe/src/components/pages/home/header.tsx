import { Stack, Typography } from "@mui/material";
import { ElementWrapper } from "../../shared/element-wrapper";
import { useIsMobileView } from "../../hooks/screen-sizes/use-is-mobile-view";
import Welcome from "../../../assets/home/willkommen.jpg";
import { useTranslation } from "react-i18next";

export const Header = () => {
  const isMobileView = useIsMobileView();
  const { t } = useTranslation();
  return (
    <ElementWrapper
      backgroundImage={{
        backgroundImage: `linear-gradient(rgba(68,68,68,0.75),rgba(68,68,68,0.75)),url(${Welcome})`,
        backgroundSize: "auto,cover",
        backgroundRepeat: "no-repeat",
        backgroundPosition: "70% 50%",
      }}
    >
      <Stack alignItems="center" p={isMobileView ? 5 : 10}>
        <Typography color="white" variant={isMobileView ? "h4" : "h2"}>
          {t("home.header.title")}
        </Typography>
        <Typography
          color="white"
          variant={isMobileView ? "body2" : "body1"}
          maxWidth={500}
          textAlign="center"
        >
          {t("home.header.body")}
        </Typography>
      </Stack>
    </ElementWrapper>
  );
};
