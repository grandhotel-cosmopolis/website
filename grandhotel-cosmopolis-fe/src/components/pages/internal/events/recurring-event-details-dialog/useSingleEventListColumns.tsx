import { Stack, Typography } from "@mui/material";
import { SingleEventDto } from "../../../../../infrastructure/generated/openapi";
import { TableColumn } from "../../../../shared/table/table";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import CheckIcon from "@mui/icons-material/Check";
import CloseIcon from "@mui/icons-material/Close";
import { SingleEventDate } from "../../../../shared/date/single-event-date";
import { SingleEventTypography } from "../../../../shared/single-event-typography";

export const useSingleEventListColumns = (): TableColumn<SingleEventDto>[] => [
  {
    id: "date",
    label: (
      <Stack direction="row">
        <CalendarTodayIcon />
        <Typography ml={2}>Date</Typography>
      </Stack>
    ),
    renderCell: (item: SingleEventDto) => (
      <SingleEventDate singleEvent={item} />
    ),
  },
  {
    id: "exception",
    label: <Typography>exception</Typography>,
    renderCell: (item: SingleEventDto) => (
      <>
        {!!item.exception ? (
          <Typography>yes</Typography>
        ) : (
          <Typography>no</Typography>
        )}
      </>
    ),
  },
  {
    id: "titleDe",
    label: "titleDe",
    renderCell: (item: SingleEventDto) => (
      <SingleEventTypography singleEvent={item}>
        {item.titleDe}
      </SingleEventTypography>
    ),
  },
  {
    id: "titleEn",
    label: "titleEn",
    renderCell: (item: SingleEventDto) => (
      <SingleEventTypography singleEvent={item}>
        {item.titleEn}
      </SingleEventTypography>
    ),
  },
  {
    id: "isPublic",
    label: "isPublic",
    renderCell: (item: SingleEventDto) =>
      item.isPublic ? <CheckIcon /> : <CloseIcon />,
  },
];
