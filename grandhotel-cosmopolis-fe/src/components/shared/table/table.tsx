import {
  Paper,
  TableContainer,
  Table as MuiTable,
  SortDirection,
} from "@mui/material";
import { TableColumn, TableHead } from "./table-head";
import { useState } from "react";
import { TableBody } from "./table-body";

type TableProps<T> = {
  readonly columns: TableColumn<T>[];
  readonly items: T[];
};

export function Table<T>(props: TableProps<T>) {
  const [order, setOrder] = useState<SortDirection>("asc");
  const [orderBy, setOrderBy] = useState<string>();

  const handleRequestSort = (
    event: React.MouseEvent<unknown>,
    property: string
  ) => {
    const isAsc = orderBy === property && order === "asc";
    setOrder(isAsc ? "desc" : "asc");
    setOrderBy(property);
  };
  return (
    <Paper sx={{ overflow: "scroll", flexGrow: 1 }}>
      <TableContainer>
        <MuiTable size="medium">
          <TableHead
            order={order}
            orderBy={orderBy}
            onRequestSort={handleRequestSort}
            columns={props.columns}
          />
          <TableBody items={props.items} columns={props.columns} />
        </MuiTable>
      </TableContainer>
    </Paper>
  );
}
