export const textFormatter = (text: string) => {
  return text.split("\n").map((str, i) => <p key={i}>{str}</p>);
};
