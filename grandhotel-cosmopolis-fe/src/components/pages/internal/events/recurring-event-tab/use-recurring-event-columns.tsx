import { Typography } from "@mui/material";
import { RecurringEventDto } from "../../../../../infrastructure/generated/openapi";
import { TableColumn } from "../../../../shared/table/table";
import CheckIcon from "@mui/icons-material/Check";
import CloseIcon from "@mui/icons-material/Close";

export const useRecurringEventColumns =
  (): TableColumn<RecurringEventDto>[] => [
    {
      id: "titleDe",
      label: "titleDe",
      renderCell: (item: RecurringEventDto) => (
        <Typography>{item.titleDe}</Typography>
      ),
    },
    {
      id: "titleEn",
      label: "titleEn",
      renderCell: (item: RecurringEventDto) => (
        <Typography>{item.titleEn}</Typography>
      ),
    },
    {
      id: "isPublic",
      label: "isPublic",
      renderCell: (item: RecurringEventDto) =>
        item.isPublic ? <CheckIcon /> : <CloseIcon />,
    },
  ];
