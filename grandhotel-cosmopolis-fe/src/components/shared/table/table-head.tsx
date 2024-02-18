import {
  Box,
  TableHead as MuiTableHead,
  SortDirection,
  TableCell,
  TableRow,
  TableSortLabel,
} from "@mui/material";
import { visuallyHidden } from "@mui/utils";
import { TableColumn } from "./table";

type TableHeadProps<T> = {
  readonly columns: TableColumn<T>[];
  readonly orderBy?: string;
  readonly order: SortDirection;
  onRequestSort: (event: React.MouseEvent<unknown>, property: string) => void;
};

export function TableHead<T>(props: TableHeadProps<T>) {
  const createSortHandler =
    (property: string) => (event: React.MouseEvent<unknown>) => {
      props.onRequestSort(event, property);
    };
  return (
    <MuiTableHead>
      <TableRow>
        {props.columns.map((c, i) => (
          <TableCell
            key={i}
            sortDirection={props.orderBy === c.id ? props.order : false}
          >
            <TableSortLabel
              active={props.orderBy === c.id}
              direction={
                props.order === false
                  ? undefined
                  : props.orderBy === c.id
                  ? props.order
                  : "asc"
              }
              onClick={createSortHandler(c.id)}
            >
              {c.label}
              {props.orderBy === c.id ? (
                <Box component="span" sx={visuallyHidden}>
                  {props.order === "desc"
                    ? "sorted descending"
                    : "sorted ascending"}
                </Box>
              ) : null}
            </TableSortLabel>
          </TableCell>
        ))}
      </TableRow>
    </MuiTableHead>
  );
}
