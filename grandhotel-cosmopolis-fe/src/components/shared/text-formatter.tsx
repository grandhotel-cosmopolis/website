import { Recurrence } from "../../infrastructure/generated/openapi";

export const textFormatter = (text: string) => {
  return text.split("\n").map((str, i) => <p key={i}>{str}</p>);
};

export const getDisplayValueForRecurrence = (recurrence: Recurrence) => {
  if (recurrence === Recurrence.XDays) {
    return "Täglich, alle";
  }
  if (recurrence === Recurrence.FirstDayInMonth) {
    return "Monatlich, jeden ersten";
  }
  if (recurrence === Recurrence.LastDayInMonth) {
    return "Monatlich, jeden letzten";
  }
  if (recurrence === Recurrence.SecondDayInMonth) {
    return "Monatlich, jeden zweiten";
  }
  if (recurrence === Recurrence.ThirdDayInMonth) {
    return "Monatlich, jeden dritten";
  }
  if (recurrence === Recurrence.MonthAtDayX) {
    return "Monatlich, am";
  }
};
