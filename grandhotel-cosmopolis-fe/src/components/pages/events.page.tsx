import {
  Badge,
  Box,
  Card,
  CardContent,
  Divider,
  Stack,
  Typography,
} from "@mui/material";
import { DateCalendar, PickersDay, PickersDayProps } from "@mui/x-date-pickers";
import { ElementWrapper } from "../shared/element-wrapper";
import { useEffect, useState } from "react";
import { useEventsQuery } from "./events/use-events-query";
import {
  getAllDatesOfSingleEvent,
  getDaysInMonth,
  getEndOfNextMonth,
  getStartOfLastMonth,
  singleEventIsInMonth,
  singleEventIsOnDate,
} from "../../services/date-time.service";
import { SingleEventView } from "../shared/events/single-events/single-event-view";
import { useIsMobileView } from "../hooks/screen-sizes/use-is-mobile-view";

function ServerDay(
  props: PickersDayProps<Date> & { highlightedDays?: number[] }
) {
  const { highlightedDays = [], day, outsideCurrentMonth, ...other } = props;

  const isSelected =
    !props.outsideCurrentMonth &&
    highlightedDays.indexOf(props.day.getDate()) >= 0;

  return (
    <Badge
      key={props.day.toString()}
      overlap="circular"
      badgeContent={
        isSelected ? (
          <Box
            sx={(theme) => ({
              width: "10px",
              height: "10px",
              backgroundColor: theme.palette.primary.main,
              borderRadius: "10px",
            })}
          />
        ) : undefined
      }
    >
      <PickersDay
        {...other}
        outsideCurrentMonth={outsideCurrentMonth}
        day={day}
      />
    </Badge>
  );
}

export const Events = () => {
  const [selectedDate, setSelectedDate] = useState(new Date());
  const [from, setFrom] = useState(getStartOfLastMonth(new Date()));
  const [to, setTo] = useState(getEndOfNextMonth(new Date()));
  const [selectedMonth, setSelectedMonth] = useState(new Date());
  const [highlightedDays, setHighlightedDays] = useState<number[]>([]);
  const { data } = useEventsQuery(from, to);
  const isMobileView = useIsMobileView();

  useEffect(() => {
    const datesWithEvents = data
      ?.filter((d) => singleEventIsInMonth(selectedMonth.getMonth(), d))
      .flatMap((se) => getAllDatesOfSingleEvent(se).map((d) => d.getDate()));
    setHighlightedDays(datesWithEvents ?? []);
  }, [data]);

  return (
    <>
      <ElementWrapper>
        <Stack>
          <DateCalendar
            value={selectedDate}
            onChange={(newValue) => setSelectedDate(newValue as Date)}
            onMonthChange={(month) => {
              const from = new Date(month);
              from.setDate(from.getDate() - 31);
              setFrom(from);
              const to = new Date(month);
              to.setDate(to.getDate() + 62);
              setTo(to);
              setSelectedMonth(month);
              setSelectedDate(
                new Date(
                  month.getFullYear(),
                  month.getMonth(),
                  Math.min(getDaysInMonth(month), selectedDate.getDate())
                )
              );
            }}
            slots={{
              day: ServerDay,
            }}
            slotProps={{
              day: { highlightedDays } as any,
            }}
          />
        </Stack>
      </ElementWrapper>
      <ElementWrapper dense>
        <Card sx={{ width: "100%", mb: 5 }}>
          <CardContent>
            <Typography>
              Events am {selectedDate.toLocaleDateString("de-DE")}
            </Typography>
            <Divider />
            {data
              ?.filter((d) => singleEventIsOnDate(selectedDate, d))
              .map((e, i) => (
                <SingleEventView
                  key={i}
                  singleEvent={e}
                  isMobileView={isMobileView}
                />
              ))}
          </CardContent>
        </Card>
      </ElementWrapper>
    </>
  );
};
