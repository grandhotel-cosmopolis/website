import { TableBody as MuiTableBody, TableCell, TableRow } from "@mui/material";
import { TableColumn } from "./table-head";

type TableBodyProps<T> = {
  readonly items: T[];
  readonly columns: TableColumn<T>[];
};

export function TableBody<T>(props: TableBodyProps<T>) {
  return (
    <MuiTableBody sx={{ height: "100%" }}>
      {props.items.map((item, index) => (
        <TableRow
          hover
          onClick={(event) => console.log("das ist wichtig")}
          tabIndex={-1}
          key={index}
          sx={{ cursor: "pointer" }}
        >
          {props.columns.map((c, i) => (
            <TableCell>{c.renderCell(item)}</TableCell>
          ))}
        </TableRow>
      ))}
    </MuiTableBody>
  );
}
