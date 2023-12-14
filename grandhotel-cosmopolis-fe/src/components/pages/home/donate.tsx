import {
  Stack,
  Card,
  CardContent,
  Typography,
  Button,
  useTheme,
} from "@mui/material";
import { Logo } from "../../../assets/general/logo";
import { ElementWrapper } from "../../shared/element-wrapper";
import { useTranslation } from "react-i18next";
import { useIsMobileView } from "../../hooks/screen-sizes/use-is-mobile-view";

export const Donate = () => {
  const theme = useTheme();
  const { t } = useTranslation();
  const isMobileView = useIsMobileView();
  return (
    <ElementWrapper fullWidthBackgroundColor={theme.palette.primary.main}>
      <Stack width="100%" alignItems="center">
        <Logo />
        <Card sx={{ width: "100%", mt: 8 }}>
          <CardContent>
            <Typography
              variant={isMobileView ? "h6" : "h5"}
              sx={(theme) => ({ color: theme.palette.text.secondary, pb: 2 })}
            >
              {t("home.donate.title")}
            </Typography>
            <Typography
              variant={isMobileView ? "body2" : "body1"}
              sx={{ whiteSpace: "pre-line" }}
            >
              {t("home.donate.body")}
            </Typography>

            <Typography
              variant={isMobileView ? "body2" : "body1"}
              sx={{ pt: 4 }}
            >
              {t("home.donate.bankAccount.number")}
            </Typography>
            <Typography variant={isMobileView ? "body2" : "body1"}>
              {t("home.donate.bankAccount.blz")}
            </Typography>
            <Typography variant={isMobileView ? "body2" : "body1"}>
              {t("home.donate.bankAccount.iban")}
            </Typography>
            <Typography variant={isMobileView ? "body2" : "body1"}>
              {t("home.donate.bankAccount.bic")}
            </Typography>
            <Button variant="outlined" sx={{ mt: 2 }}>
              {t("home.donate.donate")}
            </Button>
          </CardContent>
        </Card>
      </Stack>
    </ElementWrapper>
  );
};
