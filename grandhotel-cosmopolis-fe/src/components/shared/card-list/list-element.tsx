import {
  Card,
  CardContent,
  Typography,
  Box,
  useTheme,
  Divider,
} from "@mui/material";
import { useIsMobileView } from "../../hooks/screen-sizes/use-is-mobile-view";

export type ListElementProps = {
  readonly title: string;
  readonly image: string;
  readonly subtitle?: string;
  readonly body: string;
};

export const ListElement = (props: ListElementProps) => {
  const theme = useTheme();
  const isMobileView = useIsMobileView();
  return (
    <Card>
      <CardContent sx={{ p: 4 }}>
        <Box
          display="flex"
          justifyContent="center"
          color={theme.palette.primary.main}
          mb={2}
        >
          <Typography variant={isMobileView ? "h6" : "h4"} textAlign="center">
            {props.title}
          </Typography>
        </Box>
        <Divider />
        <Box display="flex" mt={4}>
          <Box>
            <Box>
              <img
                style={{
                  float: "left",
                  width: isMobileView ? "100%" : "50%",
                  marginRight: 32,
                  marginBottom: isMobileView ? 16 : 8,
                }}
                src={props.image}
              />
            </Box>
            {props.subtitle && (
              <Typography
                variant={isMobileView ? "h6" : "h5"}
                sx={(theme) => ({ mb: 2, color: theme.palette.text.secondary })}
              >
                {props.subtitle}
              </Typography>
            )}
            <Typography
              variant={isMobileView ? "body2" : "body1"}
              sx={{ whiteSpace: "pre-line" }}
            >
              {props.body}
            </Typography>
          </Box>
        </Box>
      </CardContent>
    </Card>
  );
};
