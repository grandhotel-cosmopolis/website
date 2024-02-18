import { TableBody as MuiTableBody, TableCell, TableRow } from "@mui/material";
import { TableColumn } from "./table";

type TableBodyProps<T> = {
  readonly items: T[];
  readonly columns: TableColumn<T>[];
  readonly onItemClick: (_: T) => void;
};

export function TableBody<T>(props: TableBodyProps<T>) {
  return (
    <MuiTableBody sx={{ height: "100%", overflow: "scroll" }}>
      {props.items.map((item, index) => (
        <TableRow
          hover
          onClick={() => props.onItemClick(item)}
          tabIndex={-1}
          key={index}
          sx={{ cursor: "pointer" }}
        >
          {props.columns.map((c, i) => (
            <TableCell key={i}>{c.renderCell(item)}</TableCell>
          ))}
        </TableRow>
      ))}
    </MuiTableBody>
  );
}
