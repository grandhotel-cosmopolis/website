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
