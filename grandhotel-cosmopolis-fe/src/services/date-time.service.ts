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
