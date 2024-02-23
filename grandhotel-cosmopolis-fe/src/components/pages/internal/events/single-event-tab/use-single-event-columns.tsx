import { Stack, Typography } from "@mui/material";
import { SingleEventDto } from "../../../../../infrastructure/generated/openapi";
import { renderDateCell } from "./single-events-tab";
import { TableColumn } from "../../../../shared/table/table";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import CheckIcon from "@mui/icons-material/Check";
import CloseIcon from "@mui/icons-material/Close";

export const useSingleEventColumns = (): TableColumn<SingleEventDto>[] => [
  {
    id: "start",
    label: (
      <Stack direction="row">
        <CalendarTodayIcon />
        <Typography ml={2}>Date</Typography>
      </Stack>
    ),
    renderCell: (item: SingleEventDto) => renderDateCell(item.start, item.end),
  },
  {
    id: "titleDe",
    label: "titleDe",
    renderCell: (item: SingleEventDto) => (
      <Typography>{item.titleDe}</Typography>
    ),
  },
  {
    id: "titleEn",
    label: "titleEn",
    renderCell: (item: SingleEventDto) => (
      <Typography>{item.titleEn}</Typography>
    ),
  },
  {
    id: "isPublic",
    label: "isPublic",
    renderCell: (item: SingleEventDto) =>
      item.isPublic ? <CheckIcon /> : <CloseIcon />,
  },
];
