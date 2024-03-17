import {
  RecurringEventDto,
  SingleEventDto,
} from "../infrastructure/generated/openapi";

export const dateString = (date?: Date) => date?.toLocaleDateString("de-DE");
export const timeString = (date?: Date) =>
  date?.toLocaleTimeString("de-DE", {
    minute: "2-digit",
    hour: "2-digit",
  });

export const formatDateTimeRange = (start?: Date, end?: Date) => {
  if (
    start?.getMonth() === end?.getMonth() &&
    start?.getDate() === start?.getDate()
  ) {
    return `${dateString(start)} ${timeString(start)} - ${timeString(end)}`;
  }
  return `${dateString(start)} ${timeString(start)} - ${dateString(
    end
  )} ${timeString(end)}`;
};

export const getStringWeekday = (weekDay?: number) => {
  switch (weekDay) {
    case 0:
      return "Sonntag";
    case 1:
      return "Montag";
    case 2:
      return "Dienstag";
    case 3:
      return "Mittwoch";
    case 4:
      return "Donnerstag";
    case 5:
      return "Freitag";
    case 6:
      return "Samstag";
    default:
      return "Invalid";
  }
};

export const isLeapYear = (date: Date) => {
  return (
    (date.getFullYear() % 4 === 0 && date.getFullYear() % 100 !== 0) ||
    date.getFullYear() % 400 === 0
  );
};

export const getDaysInMonth = (date: Date) => {
  return [
    31,
    isLeapYear(date) ? 29 : 28,
    31,
    30,
    31,
    30,
    31,
    31,
    30,
    31,
    30,
    31,
  ][date.getMonth()];
};

export const getEndOfNextMonth = (date: Date) => {
  const newDate = new Date(date.getFullYear(), date.getMonth(), 1);
  newDate.setMonth(newDate.getMonth() + 1);
  newDate.setDate(getDaysInMonth(newDate));
  return newDate;
};

export const getStartOfLastMonth = (date: Date) => {
  const newDate = new Date(date.getFullYear(), date.getMonth(), 1);
  newDate.setMonth(newDate.getMonth() - 1);
  return newDate;
};

export const getDatesBetween = (startDate: Date, stopDate: Date): Date[] => {
  var dateArray = new Array();
  var currentDate = new Date(startDate);

  while (currentDate <= stopDate) {
    dateArray.push(new Date(currentDate));
    currentDate.setDate(currentDate.getDate() + 1);
  }
  return dateArray;
};

export const getAllDatesBetween = (start?: Date, end?: Date): Date[] => {
  if (!start || !end) {
    return [];
  }
  return getDatesBetween(start, end);
};

export const getAllDatesOfSingleEvent = (singleEvent: SingleEventDto) => {
  const start = singleEvent?.exception?.start ?? singleEvent?.start;
  const end = singleEvent?.exception?.end ?? singleEvent?.end;
  return getAllDatesBetween(start, end);
};

export const isOnSameDate = (d1: Date, d2: Date) => {
  return (
    d1.getFullYear() === d2.getFullYear() &&
    d1.getMonth() === d2.getMonth() &&
    d1.getDate() === d2.getDate()
  );
};

export const isInMonth = (m: number, d: Date) => {
  return d.getMonth() === m;
};

export const singleEventIsInMonth = (
  month: number,
  singleEvent?: SingleEventDto
) => {
  const start = singleEvent?.exception?.start ?? singleEvent?.start;
  const end = singleEvent?.exception?.end ?? singleEvent?.end;
  if (!start && !end) {
    return false;
  }

  if (!start && !!end) {
    return isInMonth(month, end);
  }

  if (!!start && !end) {
    return isInMonth(month, start);
  }

  const possibleDates = getDatesBetween(start!, end!);
  for (const possibleDate of possibleDates) {
    if (isInMonth(month, possibleDate)) {
      return true;
    }
  }
  return false;
};

export const singleEventIsOnDate = (
  date: Date,
  singleEvent?: SingleEventDto
) => {
  const start = singleEvent?.exception?.start ?? singleEvent?.start;
  const end = singleEvent?.exception?.end ?? singleEvent?.end;

  if (!start && !end) {
    return false;
  }

  if (!start && !!end) {
    return isOnSameDate(end, date);
  }

  if (!!start && !end) {
    return isOnSameDate(start, date);
  }

  const possibleDates = getDatesBetween(start!, end!);
  for (const possibleDate of possibleDates) {
    if (isOnSameDate(possibleDate, date)) {
      return true;
    }
  }
  return false;
};

export const convertDatesForSingleEvents = (
  singleEvent: SingleEventDto
): SingleEventDto => {
  const test: SingleEventDto = {
    ...singleEvent,
    end: !!singleEvent.end ? new Date(singleEvent.end) : singleEvent.end,
    exception: !!singleEvent.exception
      ? {
          ...singleEvent.exception,
          start: !!singleEvent.exception.start
            ? new Date(singleEvent.exception.start)
            : singleEvent.exception.start,
          end: !!singleEvent.exception.end
            ? new Date(singleEvent.exception.end)
            : singleEvent.exception.end,
        }
      : undefined,
    start: !!singleEvent.start
      ? new Date(singleEvent.start)
      : singleEvent.start,
  };
  return test;
};

export const convertDatesForRecurringEvents = (
  recurringEvent: RecurringEventDto
): RecurringEventDto => {
  return {
    ...recurringEvent,
    endFirstOccurrence: recurringEvent.endFirstOccurrence
      ? new Date(recurringEvent.endFirstOccurrence)
      : recurringEvent.endFirstOccurrence,
    startFirstOccurrence: recurringEvent.startFirstOccurrence
      ? new Date(recurringEvent.startFirstOccurrence)
      : recurringEvent.startFirstOccurrence,
    endRecurrence: recurringEvent.endRecurrence
      ? new Date(recurringEvent.endRecurrence)
      : recurringEvent.endRecurrence,
  };
};
