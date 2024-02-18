import {
  Paper,
  TableContainer,
  Table as MuiTable,
  SortDirection,
} from "@mui/material";
import { TableHead } from "./table-head";
import { ReactElement, ReactNode, useMemo, useState } from "react";
import { TableBody } from "./table-body";

export type TableColumn<T> = {
  readonly id: string;
  readonly label: ReactNode;
  readonly renderCell: (_: T) => ReactElement;
};

type TableProps<T> = {
  readonly columns: TableColumn<T>[];
  readonly items: T[];
};

function getComparator<Key extends keyof any>(
  order: SortDirection,
  orderBy: Key
): (
  a: { [key in Key]: number | string },
  b: { [key in Key]: number | string }
) => number {
  return order === "desc"
    ? (a, b) => descendingComparator(a, b, orderBy)
    : (a, b) => -descendingComparator(a, b, orderBy);
}

function descendingComparator<T>(a: T, b: T, orderBy: keyof T) {
  if (b[orderBy] < a[orderBy]) {
    return -1;
  }
  if (b[orderBy] > a[orderBy]) {
    return 1;
  }
  return 0;
}

// Since 2020 all major browsers ensure sort stability with Array.prototype.sort().
// stableSort() brings sort stability to non-modern browsers (notably IE11). If you
// only support modern browsers you can replace stableSort(exampleArray, exampleComparator)
// with exampleArray.slice().sort(exampleComparator)
function stableSort<T>(
  array: readonly T[],
  comparator: (a: T, b: T) => number
) {
  const stabilizedThis = array.map((el, index) => [el, index] as [T, number]);
  stabilizedThis.sort((a, b) => {
    const order = comparator(a[0], b[0]);
    if (order !== 0) {
      return order;
    }
    return a[1] - b[1];
  });
  return stabilizedThis.map((el) => el[0]);
}

export function Table<T>(props: TableProps<T>) {
  const [order, setOrder] = useState<SortDirection>("asc");
  const [orderBy, setOrderBy] = useState<string>();

  const sortedRows = useMemo(
    () =>
      stableSort(
        props.items as any,
        getComparator(order, orderBy ?? "")
      ) as T[],
    [order, orderBy, props.items]
  );

  const handleRequestSort = (
    _: React.MouseEvent<unknown>,
    property: string
  ) => {
    const isAsc = orderBy === property && order === "asc";
    setOrder(isAsc ? "desc" : "asc");
    setOrderBy(property);
  };

  return (
    <Paper sx={{ flexGrow: 1, width: "100%", overflow: "hidden" }}>
      <TableContainer sx={{ overflow: "scroll", maxHeight: "100%" }}>
        <MuiTable size="medium" sx={{ minWidth: 650 }} stickyHeader>
          <TableHead
            order={order}
            orderBy={orderBy}
            onRequestSort={handleRequestSort}
            columns={props.columns}
          />
          <TableBody items={sortedRows} columns={props.columns} />
        </MuiTable>
      </TableContainer>
    </Paper>
  );
}
