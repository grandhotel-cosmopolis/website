import { Stack, Typography } from "@mui/material";
import { ElementWrapper } from "../../shared/element-wrapper";
import TogetherImage from "../../../assets/home/together.png";
import { useTranslation } from "react-i18next";

export const Together = () => {
  const { t } = useTranslation();
  return (
    <ElementWrapper>
      <Stack display="flex" alignItems="center">
        <Typography
          variant="h4"
          sx={(theme) => ({ color: theme.palette.primary.main })}
        >
          {t("home.together.title")}
        </Typography>
        <img src={TogetherImage} />
        <Typography textAlign="center" sx={{ maxWidth: 500 }}>
          {t("home.together.body")}
        </Typography>
      </Stack>
    </ElementWrapper>
  );
};
