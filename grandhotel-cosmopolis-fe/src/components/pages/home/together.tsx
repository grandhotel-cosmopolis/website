import { Stack, Typography } from "@mui/material";
import { ElementWrapper } from "../../shared/element-wrapper";
import TogetherImage from "../../../assets/home/together.png";
import { useTranslation } from "react-i18next";
import { useIsMobileView } from "../../hooks/screen-sizes/use-is-mobile-view";

export const Together = () => {
  const { t } = useTranslation();
  const isMobileView = useIsMobileView();
  return (
    <ElementWrapper>
      <Stack display="flex" alignItems="center">
        <Typography
          variant={isMobileView ? "h6" : "h4"}
          sx={(theme) => ({ color: theme.palette.primary.main })}
        >
          {t("home.together.title")}
        </Typography>
        <img src={TogetherImage} />
        <Typography
          variant={isMobileView ? "body2" : "body1"}
          textAlign="center"
          sx={{ maxWidth: 500 }}
        >
          {t("home.together.body")}
        </Typography>
      </Stack>
    </ElementWrapper>
  );
};
